<?php

namespace App\Models\Parser;

use Illuminate\Support\Collection;

abstract class AbstractParser implements ParserInterface
{
    protected Collection $groups;

    public function __construct(Collection $groups)
    {
        $this->groups = $groups;
    }

    /**
     * @param string $file
     * @param bool $needConvert
     *
     * @return Collection
     */
    abstract public function parse(string $file, bool $needConvert = true): Collection;

    abstract public function check(string $file, string $extension): bool;
}
