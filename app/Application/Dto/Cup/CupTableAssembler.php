<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Domain\Cup\Table\CupTableRow;
use App\Domain\Cup\Table\CupTableStageCell;
use App\Domain\Shared\Pagination\ArraySliceAdapter;
use App\Domain\Shared\Pagination\Slice;
use function array_map;

final readonly class CupTableAssembler
{
    /** @param list<CupTableRow> $rows @return Slice<ViewCupTableRowDto> */
    public function toRowsSlice(array $rows): Slice
    {
        return new Slice(new ArraySliceAdapter($this->toRows($rows)));
    }

    /** @param list<CupTableRow> $rows @return list<ViewCupTableRowDto> */
    public function toRows(array $rows): array
    {
        return array_map($this->toViewCupTableRowDto(...), $rows);
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
