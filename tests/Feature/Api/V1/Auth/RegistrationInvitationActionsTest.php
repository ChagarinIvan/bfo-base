<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Domain\Auth\User;
use App\Infrastructure\Sanctum\SanctumUser;
use App\Mail\PasswordMail;
use App\Mail\RegistrationUrlMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function rawurlencode;

final class RegistrationInvitationActionsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication_to_send_an_invitation(): void
    {
        $this->postJson('/api/v1/auth/registration-invitations', ['email' => 'new@example.com'])
            ->assertUnauthorized();
    }

    #[Test]
    public function it_sends_an_activation_link_for_an_authenticated_invitation(): void
    {
        Mail::fake();
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/auth/registration-invitations', ['email' => 'new@example.com'])
            ->assertNoContent();

        Mail::assertSent(RegistrationUrlMail::class, static fn (RegistrationUrlMail $mail): bool => $mail->email === 'new@example.com');
    }

    #[Test]
    public function it_activates_an_invitation_and_sends_a_password(): void
    {
        Mail::fake();
        $token = app('encrypter')->encrypt('new@example.com');

        $this->postJson('/api/v1/auth/registration-activation/' . rawurlencode($token))
            ->assertNoContent();

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        Mail::assertSent(PasswordMail::class, static fn (PasswordMail $mail): bool => $mail->email === 'new@example.com');
    }

    #[Test]
    public function it_rejects_an_invalid_activation_token(): void
    {
        $this->postJson('/api/v1/auth/registration-activation/invalid-token')
            ->assertUnprocessable();

        $this->assertSame(0, User::query()->count());
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret'),
        ]);
    }
}
