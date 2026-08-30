<?php

declare(strict_types=1);

namespace App\Domain\Auth;

final readonly class AccessToken
{
    public function __construct(public string $value, public string $type = 'Bearer')
    {
    }
}
