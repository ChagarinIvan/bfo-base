<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\LoginAction;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function is_string;

/** @see LoginAction */
final class LoginActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_token_envelope(): void
    {
        $this->createUser();

        $this->postJson('/api/v1/auth/login', ['email' => 'user@example.com', 'password' => 'secret'])
            ->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.token', static fn (mixed $token): bool => is_string($token) && $token !== '')
        ;
    }

    #[Test]
    public function it_returns_validation_error_envelope(): void
    {
        $this->postJson('/api/v1/auth/login')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.code', 'validation_error')
        ;
    }

    #[Test]
    public function it_returns_unauthorized_for_invalid_credentials(): void
    {
        $this->createUser();

        $this->postJson('/api/v1/auth/login', ['email' => 'user@example.com', 'password' => 'wrong'])
            ->assertUnauthorized()
            ->assertJsonPath('errors.0.code', 'invalid_credentials')
        ;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('secret'),
        ]);
    }
}
