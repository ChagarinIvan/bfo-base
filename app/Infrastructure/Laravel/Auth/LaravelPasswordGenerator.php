<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Auth;

use App\Domain\Auth\PasswordGenerator;
use Illuminate\Support\Str;

final class LaravelPasswordGenerator implements PasswordGenerator
{
    public function generate(): string
    {
        return Str::random(8);
    }
}
