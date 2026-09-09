<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

final readonly class ActivateRegistrationInvitation
{
    public function __construct(public string $token)
    {
    }
}
