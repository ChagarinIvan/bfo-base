<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Mail\RegistrationUrlMail;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Routing\UrlGenerator;
use function rawurlencode;

final readonly class SendRegistrationInvitationService
{
    public function __construct(
        private Encrypter $encrypter,
        private Mailer $mailer,
        private UrlGenerator $urlGenerator,
    ) {
    }

    public function execute(SendRegistrationInvitation $command): void
    {
        $token = $this->encrypter->encrypt($command->email);
        $url = $this->urlGenerator->to('/app/registration/activate/' . rawurlencode($token));

        $this->mailer->send(new RegistrationUrlMail($command->email, $url));
    }
}
