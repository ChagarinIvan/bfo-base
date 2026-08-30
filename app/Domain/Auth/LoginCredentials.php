<?php

declare(strict_types=1);

namespace App\Domain\Auth;

final readonly class LoginCredentials
{
    public function __construct(public string $email, public string $password)
    {
    }
}
