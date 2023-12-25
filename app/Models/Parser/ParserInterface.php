<?php
declare(strict_types=1);

namespace App\Models\Parser;

use Illuminate\Support\Collection;

interface ParserInterface
{
    /**
     * @param string $file
     * @param bool $needConvert
     *
     * @return Collection
     */
    public function parse(string $file, bool $needConvert = true): Collection;

    public function check(string $file, string $extension): bool;
}
