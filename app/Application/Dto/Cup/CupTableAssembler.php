<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Domain\Cup\Table\CupTable;
use App\Domain\Cup\Table\CupTableRow;
use App\Domain\Cup\Table\CupTableStage;
use App\Domain\Cup\Table\CupTableStageCell;
use function array_map;

final readonly class CupTableAssembler
{
    public function toViewCupTableDto(CupTable $table): ViewCupTableDto
    {
        return new ViewCupTableDto(
            stages: array_map($this->toViewCupTableStageDto(...), $table->stages),
            rows: array_map($this->toViewCupTableRowDto(...), $table->rows),
        );
    }

    private function toViewCupTableStageDto(CupTableStage $stage): ViewCupTableStageDto
    {
        return new ViewCupTableStageDto(
            stageId: $stage->stageId,
            eventId: $stage->eventId,
            date: $stage->date,
            name: $stage->name,
        );
    }

    private function toViewCupTableRowDto(CupTableRow $row): ViewCupTableRowDto
    {
        return new ViewCupTableRowDto(
            place: $row->place,
            personId: $row->personId,
            personName: $row->personName,
            personYear: $row->personYear,
            clubName: $row->clubName,
            stages: array_map($this->toViewCupTableStageCellDto(...), $row->stages),
            totalPoints: $row->totalPoints,
            averagePoints: $row->averagePoints,
        );
    }

    private function toViewCupTableStageCellDto(CupTableStageCell $cell): ViewCupTableStageCellDto
    {
        return new ViewCupTableStageCellDto(
            stageId: $cell->stageId,
            points: $cell->points,
            counted: $cell->counted,
            distanceId: $cell->distanceId,
            protocolLineId: $cell->protocolLineId,
        );
    }
}
