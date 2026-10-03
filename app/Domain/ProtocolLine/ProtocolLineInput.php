<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

final readonly class ProtocolLineInput
{
    public function __construct(
        public int $serialNumber,
        public string $lastname,
        public string $firstname,
        public string $club,
        public ?int $year,
        public ?string $rank,
        public int $runnerNumber,
        public ?string $time,
        public ?int $place,
        public ?string $completeRank,
        public ?int $points,
        public bool $vk,
        public string $group,
        public string $normalizedGroupName,
        public int $distanceLength,
        public int $distancePoints,
        public string $preparedLine = '',
        public bool $activateRank = false,
    ) {
    }
}
