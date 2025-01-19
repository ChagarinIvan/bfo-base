<?php

declare(strict_types=1);

namespace App\Domain\Rank;

interface JuniorThirdRankChecker
{
    public function check(int $personId): ?Rank;
}
