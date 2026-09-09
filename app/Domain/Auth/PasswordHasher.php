<?php

declare(strict_types=1);

namespace App\Domain\Auth;

interface PasswordHasher
{
    public function hash(string $password): string;
}
