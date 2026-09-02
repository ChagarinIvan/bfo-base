<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Rank\Rank;
use Carbon\Carbon;

final readonly class PersonRankHistory
{
    public function __construct(
        public int $protocolLineId,
        public int $distanceId,
        public int $eventId,
        public int $competitionId,
        public Rank $rank,
        public RankChangeType $changeType,
        public Carbon $achievedOn,
        public ?Carbon $activatedOn,
        public Carbon $startedOn,
        public ?Carbon $finishedOn,
    ) {
    }
}
