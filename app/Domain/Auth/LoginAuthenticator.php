<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Auth\Exception\InvalidCredentials;

interface LoginAuthenticator
{
    /** @throws InvalidCredentials */
    public function authenticate(LoginCredentials $credentials): AccessToken;
}
