<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

final readonly class ExportCupTableDto
{
    /** @param list<ExportCupTableSectionDto> $sections */
    public function __construct(
        public string $cupName,
        public int $cupId,
        public array $sections,
    ) {
    }
}
