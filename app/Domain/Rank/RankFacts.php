<?php

declare(strict_types=1);

namespace App\Domain\Rank;

interface RankFacts
{
    /** @return list<RankAchievement> */
    public function forPerson(int $personId): array;
}
