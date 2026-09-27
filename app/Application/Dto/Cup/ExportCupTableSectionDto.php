<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

final readonly class ExportCupTableSectionDto
{
    /**
     * @param list<ExportCupTableStageDto> $stages
     * @param list<ViewCupTableRowDto> $rows
     */
    public function __construct(
        public string $groupName,
        public array $stages,
        public array $rows,
    ) {
    }
}
