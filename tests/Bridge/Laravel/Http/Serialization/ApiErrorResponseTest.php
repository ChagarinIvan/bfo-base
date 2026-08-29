<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Serialization;

use App\Application\Exception\AuthenticationFailed;
use App\Bridge\Laravel\Http\Serialization\ApiErrorResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiErrorResponseTest extends TestCase
{
    #[Test]
    public function it_serializes_http_error_attribute(): void
    {
        $response = new ApiErrorResponse()->fromException(new AuthenticationFailed());

        $this->assertSame(401, $response->getStatusCode());

        $this->assertSame([
            'errors' => [[
                'code' => 'invalid_credentials',
                'message' => 'The provided credentials are incorrect.',
            ]],
        ], $response->getData(true));
    }
}
