<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Shared;
use function strtr;

trait EscapesLikePatterns
{
    private function escapeLikePattern(string $value): string
    {
        return strtr($value, ['!' => '!!', '%' => '!%', '_' => '!_']);
    }
}
