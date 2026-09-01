<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use Carbon\CarbonImmutable;

final readonly class RankAchievement
{
    public function __construct(
        public int $personId,
        public int $protocolLineId,
        public int $distanceId,
        public int $eventId,
        public int $competitionId,
        public Rank $rank,
        public CarbonImmutable $achievedOn,
        public ?CarbonImmutable $activatedOn,
        public bool $massCompetition = false,
        public bool $outOfCompetition = false,
        public ?CarbonImmutable $birthday = null,
        public bool $hasResult = true,
    ) {
    }
}
