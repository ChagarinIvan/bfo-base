<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Domain\Auth\InvalidEmail;
use App\Domain\Auth\UserRegistrationService;
use App\Mail\PasswordMail;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Validation\ValidationException;

final readonly class ActivateRegistrationInvitationService
{
    public function __construct(
        private Encrypter $encrypter,
        private UserRegistrationService $registrations,
        private Mailer $mailer,
    ) {
    }

    /** @throws ValidationException */
    public function execute(ActivateRegistrationInvitation $command): void
    {
        try {
            $email = $this->encrypter->decrypt($command->token);
        } catch (DecryptException) {
            throw ValidationException::withMessages(['token' => ['Invalid registration invitation.']]);
        }

        try {
            $password = $this->registrations->register($email);
        } catch (InvalidEmail) {
            throw ValidationException::withMessages(['email' => ['The email field must be a valid email address.']]);
        }

        $this->mailer->send(new PasswordMail($email, $password));
    }
}
