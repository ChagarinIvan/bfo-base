<?php

declare(strict_types=1);

namespace App\Domain\Person;

interface RankFactsCollector
{
    /** @return list<RankFact> */
    public function collect(int $personId): array;
}
