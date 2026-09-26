<?php

declare(strict_types=1);

namespace App\Domain\Cup\Table;

final readonly class CupTableRow
{
    /** @param array<int|string, CupTableStageCell> $stages */
    public function __construct(
        public int $place,
        public string $personId,
        public string $personName,
        public int $personYear,
        public string $clubName,
        public array $stages,
        public string $totalPoints,
        public string $averagePoints,
    ) {
    }
}
