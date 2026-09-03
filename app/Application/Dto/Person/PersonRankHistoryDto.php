<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

final readonly class PersonRankHistoryDto
{
    public function __construct(
        public string $id,
        public string $personId,
        public string $protocolLineId,
        public string $distanceId,
        public string $eventId,
        public string $competitionId,
        public int $rankId,
        public string $changeType,
        public string $achievedOn,
        public ?string $activatedOn,
        public string $startedOn,
        public ?string $finishedOn,
        public string $rank,
        public ?string $eventDate,
        public ?string $competitionName,
        public ?string $eventName,
    ) {
    }
}
