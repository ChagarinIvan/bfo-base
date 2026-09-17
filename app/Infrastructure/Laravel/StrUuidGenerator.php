<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel;

use App\Domain\Shared\UuidGenerator;
use Illuminate\Support\Str;

final class StrUuidGenerator implements UuidGenerator
{
    public function generate(): string
    {
        return (string) Str::uuid();
    }
}
