<?php

declare(strict_types=1);

namespace App\Models\Parser;

use Illuminate\Support\Collection;

interface ParserInterface
{
    public function parse(string $file): Collection;

    public function check(string $file, string $extension): bool;
}
