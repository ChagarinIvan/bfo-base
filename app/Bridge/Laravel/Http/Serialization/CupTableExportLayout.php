<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Cup\ExportCupTableSectionDto;
use App\Application\Dto\Cup\ExportCupTableStageDto;
use App\Application\Dto\Cup\ViewCupTableRowDto;
use function array_map;
use function substr;

final readonly class CupTableExportLayout
{
    /** @return list<string> */
    public function headings(ExportCupTableSectionDto $section): array
    {
        return [
            '№',
            'Прозвішча, Імя',
            'Год',
            ...array_map($this->stageDate(...), $section->stages),
            'Ачкі',
            'Сярэдняе',
            'Месца',
        ];
    }

    /** @return list<string|int> */
    public function values(ViewCupTableRowDto $row, ExportCupTableSectionDto $section, int $number): array
    {
        $points = [];
        foreach ($section->stages as $stage) {
            $points[] = $row->stages[$stage->stageId]->points ?? '';
        }

        return [
            $number,
            $row->personName,
            $row->personYear,
            ...$points,
            $row->totalPoints,
            $row->averagePoints,
            $row->place,
        ];
    }

    private function stageDate(ExportCupTableStageDto $stage): string
    {
        return substr($stage->date, 8, 2) . '.' . substr($stage->date, 5, 2);
    }
}
