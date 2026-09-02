<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Rank\Rank;
use Carbon\Carbon;

/** Факт что в протоколы был выполненный разряд */
final readonly class RankFact
{
    public function __construct(
        public int $protocolLineId,
        public int $distanceId,
        public int $eventId,
        public int $competitionId,
        public Rank $rank,
        public Carbon $achievedOn,
        public ?Carbon $activatedOn,
    ) {
    }
}
