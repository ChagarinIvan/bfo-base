<?php

declare(strict_types=1);

namespace App\Models\Parser;

use Illuminate\Support\Collection;

abstract class AbstractParser implements ParserInterface
{
    abstract public function parse(string $file): Collection;

    abstract public function check(string $file, string $extension): bool;

    public function __construct(protected Collection $groups)
    {
    }
}
