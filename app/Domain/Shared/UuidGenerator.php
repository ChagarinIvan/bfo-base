<?php

declare(strict_types=1);

namespace App\Domain\Shared;

interface UuidGenerator
{
    public function generate(): string;
}
