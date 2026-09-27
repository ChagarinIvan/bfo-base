<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

interface RankCheckPersonSnapshotReader
{
    /**
     * @param list<int> $personIds
     *
     * @return array<int, RankCheckPersonSnapshot>
     */
    public function read(array $personIds): array;
}
