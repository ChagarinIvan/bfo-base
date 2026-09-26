<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

final readonly class ViewCupTableRowDto
{
    /** @param array<int|string, ViewCupTableStageCellDto> $stages */
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
