<?php

namespace App\Models\Parser;

use Illuminate\Database\Eloquent\Collection;

interface ParserInterface
{
    /**
     * @param string $file
     * @param bool $needConvert
     * @return Collection
     * @throw \App\Exceptions\ParsingException
     */
    public function parse(string $file, bool $needConvert = true): Collection;

    public function check(string $file): bool;
}
