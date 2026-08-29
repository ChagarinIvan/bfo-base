<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\ListUsersAction;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see ListUsersAction */
final class ListUsersActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_all_users_without_passwords(): void
    {
        $user = $this->createUser('user@example.com');
        $otherUser = $this->createUser('other@example.com');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $user->id, 'email' => $user->email])
            ->assertJsonFragment(['id' => $otherUser->id, 'email' => $otherUser->email])
            ->assertJsonMissingPath('data.0.password')
        ;
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->getJson('/api/v1/users')->assertUnauthorized();
    }

    private function createUser(string $email): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => $email,
            'password' => Hash::make('secret'),
        ]);
    }
}
