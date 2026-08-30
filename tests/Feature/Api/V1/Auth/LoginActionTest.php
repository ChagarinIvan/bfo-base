<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\LoginAction;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('token', static fn (mixed $token): bool => is_string($token) && $token !== '')
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

    #[Test]
    public function an_expired_token_is_rejected(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('expired-token');
        $token->accessToken->forceFill(['expires_at' => now()->subMinute()])->save();

        $this->withToken($token->plainTextToken)
            ->getJson('/api/v1/users')
            ->assertUnauthorized()
            ->assertJsonPath('errors.0.code', 'unauthenticated')
        ;
    }

    #[Test]
    public function login_works_after_repairing_a_legacy_sanctum_table(): void
    {
        Schema::table('personal_access_tokens', static function (Blueprint $table): void {
            $table->dropColumn('expires_at');
        });

        $migration = require base_path(
            'database/migrations/2026_08_29_000001_add_expires_at_to_personal_access_tokens_table.php',
        );
        $migration->up();

        $this->assertTrue(Schema::hasColumn('personal_access_tokens', 'expires_at'));
        $this->createUser();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'secret',
        ])->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
        ;
    }

    #[Test]
    public function login_is_rate_limited(): void
    {
        $this->createUser();

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'user@example.com',
                'password' => 'secret',
            ])->assertOk();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'secret',
        ])->assertTooManyRequests();
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('secret'),
        ]);
    }
}
