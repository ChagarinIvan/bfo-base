<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use Carbon\CarbonImmutable;

final readonly class PersonRankHistory
{
    public function __construct(
        public int $protocolLineId,
        public int $distanceId,
        public int $eventId,
        public int $competitionId,
        public Rank $rank,
        public string $changeType,
        public CarbonImmutable $achievedOn,
        public ?CarbonImmutable $activatedOn,
        public CarbonImmutable $startedOn,
        public ?CarbonImmutable $finishedOn,
    ) {
    }
}
