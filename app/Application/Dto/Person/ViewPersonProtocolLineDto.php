<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\AbstractDto;
use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Event\EventInfoDto;

final class ViewPersonProtocolLineDto
{
    public function __construct(
        public string $id,
        public string $firstname,
        public string $lastname,
        public string $distanceId,
        public string $competitionId,
        public string $competitionName,
        public string $eventName,
        public string $eventDate,
        public string $groupName,
        public ?string $year,
        public ?string $time,
        public ?string $place,
        public ?string $completeRank,
    ) {
    }
}
