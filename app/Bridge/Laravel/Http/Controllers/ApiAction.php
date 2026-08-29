<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers;

use App\Application\Dto\AbstractDto;
use App\Application\Dto\Auth\UserId;
use App\Application\Exception\ApplicationException;
use App\Bridge\Laravel\Http\Serialization\ApiDtoSerializer;
use App\Bridge\Laravel\Http\Serialization\ApiErrorResponse;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory as Validator;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use function array_map;
use function array_merge;

trait ApiAction
{
    public function __construct(
        private readonly Request $request,
        private readonly Validator $validator,
        Container $container,
        private readonly ApiDtoSerializer $serializer,
        private readonly ApiErrorResponse $errorResponse,
    )
    {
        if ($request->user()) {
            $container->instance(UserId::class, new UserId($request->user()->id));
        }
    }

    public function callAction($method, $parameters): mixed
    {
        $injected = [];
        foreach ($parameters as $parameter) {
            if (!$parameter instanceof AbstractDto) {
                $injected[] = $parameter;
                continue;
            }
            try {
                $validated = $parameter::requestValidationRules() === []
                    ? []
                    : $this->request->validate($parameter::requestValidationRules());
            } catch (ValidationException $exception) {
                return response()->json(['errors' => collect($exception->errors())
                    ->flatMap(static fn (array $messages, string $field): array => array_map(
                        static fn (string $message): array => [
                            'code' => 'validation_error',
                            'field' => $field,
                            'message' => $message,
                        ],
                        $messages,
                    ))
                    ->values()
                    ->all()], 422);
            }
            if ($parameter::parametersValidationRules() !== []) {
                $validated = array_merge(
                    $this->validator->validate($parameters, $parameter::parametersValidationRules()),
                    $validated,
                );
            }
            $injected[] = $parameter->fromArray($validated);
        }
        try {
            $result = parent::callAction($method, $injected);
        } catch (ApplicationException $exception) {
            return $this->errorResponse->fromException($exception);
        }

        if ($result instanceof Response) {
            return $result;
        }

        $status = new ReflectionClass($this)->getAttributes(ResponseStatus::class);
        $serialized = $this->serializer->serialize(
            $result,
            $this->request->user() ? 'authenticated' : 'public',
        );

        $response = response()->json(
            $serialized,
            $status === [] ? Response::HTTP_OK : $status[0]->newInstance()->status,
        );

        if ($result instanceof Slice) {
            foreach ($result->paginationHeaders() as $header => $value) {
                $response->header($header, (string) $value);
            }
        }

        return $response;
    }
}
