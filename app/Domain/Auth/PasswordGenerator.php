<?php

declare(strict_types=1);

namespace App\Domain\Auth;

interface PasswordGenerator
{
    public function generate(): string;
}
