<?php

declare(strict_types=1);

namespace App\Domain\Person\Audit;

use App\Domain\Person\RankChangeType;
use App\Domain\Rank\Rank;
use Carbon\Carbon;

final readonly class RankHistorySnapshot
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

    public function equals(self $other): bool
    {
        return $this->protocolLineId === $other->protocolLineId
            && $this->distanceId === $other->distanceId
            && $this->eventId === $other->eventId
            && $this->competitionId === $other->competitionId
            && $this->rank === $other->rank
            && $this->changeType === $other->changeType
            && $this->achievedOn->toDateString() === $other->achievedOn->toDateString()
            && $this->activatedOn?->toDateString() === $other->activatedOn?->toDateString()
            && $this->startedOn->toDateString() === $other->startedOn->toDateString()
            && $this->finishedOn?->toDateString() === $other->finishedOn?->toDateString();
    }
}
