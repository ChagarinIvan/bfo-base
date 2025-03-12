<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use Carbon\Carbon;

interface JuniorThirdRankChecker
{
    public function check(int $personId, ?Carbon $date = null): ?Rank;
}
