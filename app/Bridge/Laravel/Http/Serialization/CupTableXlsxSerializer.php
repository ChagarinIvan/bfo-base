<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Cup\ExportCupTableDto;
use App\Application\Dto\Cup\ExportCupTableSectionDto;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use function array_map;
use function count;
use function in_array;
use function is_numeric;
use function mb_strtolower;
use function mb_substr;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function str_replace;
use function strlen;
use function trim;

final readonly class CupTableXlsxSerializer
{
    public function __construct(private CupTableExportLayout $layout)
    {
    }

    public function serialize(ExportCupTableDto $export): string
    {
        $book = new Spreadsheet();
        $usedNames = [];

        try {
            foreach ($export->sections as $index => $section) {
                $sheet = $index === 0 ? $book->getActiveSheet() : $book->createSheet();
                $sheet->setTitle($this->sheetName($section->groupName, $usedNames));
                $this->fillSheet($sheet, $section);
            }
            if ($export->sections === []) {
                $book->getActiveSheet()->setTitle('Кубак');
            }

            $book->setActiveSheetIndex(0);
            ob_start();
            try {
                new Xlsx($book)->save('php://output');
                $result = ob_get_contents();
            } finally {
                ob_end_clean();
            }
            if ($result === false) {
                throw new RuntimeException('Cannot create XLSX export');
            }

            return $result;
        } finally {
            $book->disconnectWorksheets();
        }
    }

    /** @param list<string> $usedNames */
    private function sheetName(string $name, array &$usedNames): string
    {
        $base = trim(str_replace(['[', ']', ':', '*', '?', '/', '\\'], ' ', $name));
        $base = mb_substr($base === '' ? 'Група' : $base, 0, 31);
        $candidate = $base;
        $suffix = 2;
        while (in_array(mb_strtolower($candidate), array_map(mb_strtolower(...), $usedNames), true)) {
            $ending = '-' . $suffix++;
            $candidate = mb_substr($base, 0, 31 - strlen($ending)) . $ending;
        }
        $usedNames[] = $candidate;

        return $candidate;
    }

    private function fillSheet(Worksheet $sheet, ExportCupTableSectionDto $section): void
    {
        $headings = $this->layout->headings($section);
        foreach ($headings as $index => $heading) {
            $cell = Coordinate::stringFromColumnIndex($index + 1) . '1';
            $sheet->setCellValueExplicit($cell, $heading, DataType::TYPE_STRING);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index + 1))->setAutoSize(true);
        }

        foreach ($section->rows as $rowIndex => $row) {
            foreach ($this->layout->values($row, $section, $rowIndex + 1) as $columnIndex => $value) {
                $cell = Coordinate::stringFromColumnIndex($columnIndex + 1) . ($rowIndex + 2);
                if ($columnIndex === 1 || $value === '' || !is_numeric($value)) {
                    $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValueExplicit($cell, (float) $value, DataType::TYPE_NUMERIC);
                }
            }
            foreach ($section->stages as $stageIndex => $stage) {
                $stageCell = $row->stages[$stage->stageId] ?? null;
                if ($stageCell?->counted === true) {
                    $cell = Coordinate::stringFromColumnIndex($stageIndex + 4) . ($rowIndex + 2);
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                }
            }
        }

        $sheet->freezePane('D2');
        $sheet->setAutoFilter('A1:' . Coordinate::stringFromColumnIndex(count($headings)) . (count($section->rows) + 1));
    }
}
