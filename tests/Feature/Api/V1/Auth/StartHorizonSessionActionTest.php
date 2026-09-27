<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\StartHorizonSessionAction;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see StartHorizonSessionAction */
final class StartHorizonSessionActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_exchanges_a_bearer_token_for_a_web_session_that_can_open_horizon(): void
    {
        $user = $this->createUser();
        config()->set('horizon.authorized_user_id', $user->id);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/auth/horizon-session')
            ->assertNoContent()
            ->assertCookie(config('session.cookie'))
        ;

        $sessionId = $response->getCookie(config('session.cookie'))?->getValue();

        $this->assertNotNull($sessionId);
        app('auth')->forgetGuards();
        $this->withoutToken()
            ->withUnencryptedCookie(config('session.cookie'), $sessionId)
            ->get('/horizon')
            ->assertOk()
        ;
    }

    #[Test]
    public function it_requires_a_bearer_token(): void
    {
        $this->postJson('/api/v1/auth/horizon-session')->assertUnauthorized();
    }

    #[Test]
    public function it_denies_a_user_other_than_the_configured_horizon_user(): void
    {
        $this->createUser();
        $user = $this->createUser();
        config()->set('horizon.authorized_user_id', 1);
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/horizon-session')
            ->assertForbidden()
            ->assertJsonPath('errors.0.code', 'horizon_access_denied')
        ;

        $this->actingAs($user)
            ->get('/horizon')
            ->assertForbidden()
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
