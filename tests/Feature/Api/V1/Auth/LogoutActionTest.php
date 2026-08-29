<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\LogoutAction;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see LogoutAction */
final class LogoutActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_revokes_current_token(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->deleteJson('/api/v1/auth/logout')->assertNoContent();
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->deleteJson('/api/v1/auth/logout')->assertUnauthorized();
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
