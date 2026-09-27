<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Cup\ExportCupTableDto;
use App\Application\Dto\Cup\ExportCupTableStageDto;
use RuntimeException;
use function array_map;
use function fclose;
use function fopen;
use function fputcsv;
use function rewind;
use function str_replace;
use function stream_get_contents;

final readonly class CupTableCsvSerializer
{
    public function serialize(ExportCupTableDto $export): string
    {
        $stream = fopen('php://temp', 'w+b');

        if ($stream === false) {
            throw new RuntimeException('Unable to open CSV stream.');
        }

        foreach ($export->sections as $section) {
            fputcsv($stream, [$section->groupName], ';', '"', '');
            fputcsv($stream, [
                'Место', 'ФИО', 'Год', 'Клуб', 'Очки',
                ...array_map(static fn (ExportCupTableStageDto $stage): string => $stage->date, $section->stages),
            ], ';', '"', '');

            foreach ($section->rows as $row) {
                $stagePoints = [];
                foreach ($section->stages as $stage) {
                    $cell = $row->stages[$stage->stageId] ?? null;
                    $stagePoints[] = $cell === null ? '' : $cell->points;
                }
                fputcsv($stream, [
                    $row->place,
                    $row->personName,
                    $row->personYear,
                    $row->clubName,
                    $row->totalPoints,
                    ...$stagePoints,
                ], ';', '"', '');
            }
            fputcsv($stream, [], ';', '"', '');
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        if ($csv === false) {
            throw new RuntimeException('Unable to read CSV stream.');
        }

        return str_replace("\n", "\r\n", str_replace("\r\n", "\n", $csv));
    }
}
