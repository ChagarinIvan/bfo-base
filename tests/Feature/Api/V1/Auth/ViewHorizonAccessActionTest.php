<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\ViewHorizonAccessAction;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see ViewHorizonAccessAction */
final class ViewHorizonAccessActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_the_horizon_capability_for_the_authenticated_user(): void
    {
        $allowedUser = $this->createUser();
        $deniedUser = $this->createUser();
        config()->set('horizon.authorized_user_id', $allowedUser->id);

        $this->withToken($allowedUser->createToken('allowed')->plainTextToken)
            ->getJson('/api/v1/auth/horizon-access')
            ->assertOk()
            ->assertExactJson(['allowed' => true])
        ;

        app('auth')->forgetGuards();
        $this->withToken($deniedUser->createToken('denied')->plainTextToken)
            ->getJson('/api/v1/auth/horizon-access')
            ->assertOk()
            ->assertExactJson(['allowed' => false])
        ;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
