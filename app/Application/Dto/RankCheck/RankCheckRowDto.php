<?php

declare(strict_types=1);

namespace App\Application\Dto\RankCheck;

final readonly class RankCheckRowDto
{
    public function __construct(
        public int $position,
        public ?string $group,
        public string $name,
        public ?string $club,
        public ?string $rank,
        public ?string $number,
        public ?string $year,
        public ?string $personId,
        public ?string $databaseName,
        public ?string $databaseClub,
        public ?string $databaseRank,
        public ?string $databaseYear,
        public bool $hasPerson,
        public bool $isEqual,
    ) {
    }
}
