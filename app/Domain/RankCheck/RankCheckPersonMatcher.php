<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

interface RankCheckPersonMatcher
{
    /**
     * It returns list <preparedLine, personId> if person matched
     * @param list<string> $preparedLines
     *
     * @return array<string, int>
     */
    public function match(array $preparedLines): array;
}
