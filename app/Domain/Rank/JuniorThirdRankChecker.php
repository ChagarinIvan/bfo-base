<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use Carbon\Carbon;

interface JuniorThirdRankChecker
{
    public function check(int $personId, ?Carbon $date = null): ?Rank;

    /**
     * Прогревает протокольные линии для пачки персон одним запросом,
     * чтобы последующие check() не ходили в базу по одной персоне.
     *
     * @param int[] $personIds
     */
    public function warmUp(array $personIds, ?Carbon $date = null): void;
}
