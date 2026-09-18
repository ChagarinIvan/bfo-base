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
use Illuminate\Routing\Router;
use Illuminate\Validation\Factory as Validator;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use function array_map;
use function array_merge;
use function is_bool;

trait ApiAction
{
    public function __construct(
        private readonly Router $router,
        private readonly Validator $validator,
        private readonly Container $container,
        private readonly ApiDtoSerializer $serializer,
        private readonly ApiErrorResponse $errorResponse,
    )
    {
    }

    public function callAction($method, $parameters): mixed
    {
        $request = $this->router->getCurrentRequest();
        $injected = [];
        foreach ($parameters as $parameter) {
            if (!$parameter instanceof AbstractDto) {
                $injected[] = $parameter;
                continue;
            }
            try {
                $requestData = $parameter::normaliseRequestData($request->all());
                $validated = $parameter::requestValidationRules() === []
                    ? []
                    : $this->validator->validate(
                        $requestData,
                        $parameter::requestValidationRules(),
                    );
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
            $request->user() ? 'authenticated' : 'public',
        );

        $response = response()->json(
            $serialized,
            $status === [] ? Response::HTTP_OK : $status[0]->newInstance()->status,
        );

        if ($result instanceof Slice) {
            foreach ($result->paginationHeaders() as $header => $value) {
                $response->header(
                    $header,
                    is_bool($value) ? ($value ? 'true' : 'false') : (string) $value,
                );
            }
        }

        return $response;
    }

    protected function userId(): ?UserId
    {
        return $this->container->has(UserId::class) ? $this->container->get(UserId::class) ?? null : null;
    }
}
