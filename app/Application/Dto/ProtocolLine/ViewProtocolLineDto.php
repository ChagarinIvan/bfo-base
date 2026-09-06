<?php

declare(strict_types=1);

namespace App\Application\Dto\ProtocolLine;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Serialization\Groups;

final readonly class ViewProtocolLineDto
{
    public function __construct(
        public string $id,
        public string $personId,
        public string $firstname,
        public string $lastname,
        public string $distanceId,
        public ?string $eventId,
        public ?string $competitionId,
        public ?string $competitionName,
        public ?string $eventName,
        public ?string $eventDate,
        public ?string $groupName,
        public ?string $year,
        public ?string $time,
        public ?string $place,
        public ?string $completeRank,
        #[Groups(['authenticated'])]
        public ?ImpressionDto $created = null,
        #[Groups(['authenticated'])]
        public ?ImpressionDto $updated = null,
    ) {
    }
}
