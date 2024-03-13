<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Dto\AbstractDto;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class Controller extends BaseController
{
    public function __construct(
        private readonly Request $request,
        protected Redirector $redirector,
    ) {
    }

    /**
     * @param string $method
     * @param array<int, mixed> $parameters
     */
    public function callAction($method, $parameters): Response
    {
        $injectParams = [];
        foreach ($parameters as $parameter) {
            if ($parameter instanceof AbstractDto) {
                try {
                    $validated = $this->request->validate($parameter::validationRules());
                } catch (ValidationException $e) {
                    throw new BadRequestHttpException($e->getMessage(), previous: $e);
                }
                $parameter = $parameter->fromArray($validated);
            }
            $injectParams[] = $parameter;
        }

        return parent::callAction($method, $injectParams);
    }
}
