<?php

declare(strict_types=1);

namespace App\Application\Dto\Rank;

final readonly class ViewRankDto
{
    public function __construct(
        public string $id,
        public string $rank,
        public ?string $eventId,
        public string $startDate,
        public string $finishDate,
        public ?string $activatedDate,
        public string $personId,

        // from other aggregates
        public string $personFirstname,
        public string $personLastname,
        public ?string $distanceId,
        public ?string $protocolLineId,
        public ?string $competitionName,
        public ?string $eventName,
        public ?string $eventDate,
    ) {
    }
}
