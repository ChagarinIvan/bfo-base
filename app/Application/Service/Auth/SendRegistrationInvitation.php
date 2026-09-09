<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

final readonly class SendRegistrationInvitation
{
    public function __construct(public string $email)
    {
    }
}
