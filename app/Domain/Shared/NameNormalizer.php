<?php

declare(strict_types=1);

namespace App\Domain\Shared;

interface NameNormalizer
{
    public function normalize(string $name): string;
}
