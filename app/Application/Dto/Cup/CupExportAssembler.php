<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Domain\Cup\Cup;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Table\CupTable;
use App\Domain\Cup\Table\CupTableStage;
use function array_map;

final readonly class CupExportAssembler
{
    public function __construct(private CupTableAssembler $tables)
    {
    }

    /** @param list<array{group: CupGroup, table: CupTable}> $sections */
    public function toDto(Cup $cup, array $sections): ExportCupTableDto
    {
        return new ExportCupTableDto(
            cupName: $cup->name,
            cupId: $cup->id,
            sections: array_map(fn (array $section): ExportCupTableSectionDto => new ExportCupTableSectionDto(
                groupName: $section['group']->name(),
                stages: array_map(static fn (CupTableStage $stage): ExportCupTableStageDto => new ExportCupTableStageDto(
                    $stage->stageId,
                    $stage->date,
                ), $section['table']->stages),
                rows: $this->tables->toRows($section['table']->rows),
            ), $sections),
        );
    }
}
