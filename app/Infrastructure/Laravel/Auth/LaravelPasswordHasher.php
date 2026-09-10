<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Auth;

use App\Domain\Auth\PasswordHasher;
use Illuminate\Contracts\Hashing\Hasher;

final readonly class LaravelPasswordHasher implements PasswordHasher
{
    public function __construct(private Hasher $hasher)
    {
    }

    public function hash(string $password): string
    {
        return $this->hasher->make($password);
    }
}
