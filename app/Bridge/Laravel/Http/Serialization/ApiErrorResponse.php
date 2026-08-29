<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Serialization;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;
use Illuminate\Http\JsonResponse;
use ReflectionClass;

final readonly class ApiErrorResponse
{
    public function fromException(ApplicationException $exception): JsonResponse
    {
        $reflection = new ReflectionClass($exception);
        $attributes = $reflection->getAttributes(HttpError::class);

        if ($attributes === []) {
            return response()->json(['errors' => [[
                'code' => 'application_error',
                'message' => $exception->getMessage(),
            ]]], 500);
        }

        $error = $attributes[0]->newInstance();

        return response()->json(['errors' => [[
            'code' => $error->code,
            'message' => $exception->getMessage(),
        ]]], $error->status);
    }
}
