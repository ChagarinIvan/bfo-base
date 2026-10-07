<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Cup\ExportCupTableDto;
use App\Application\Dto\Cup\ExportCupTableSectionDto;
use App\Application\Dto\Cup\ExportCupTableStageDto;
use App\Application\Dto\Cup\ViewCupTableRowDto;
use function htmlspecialchars;
use function implode;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

final readonly class CupTableHtmlSerializer
{
    public function serialize(ExportCupTableDto $export): string
    {
        $html = [
            '<!doctype html>',
            '<html lang="ru">',
            '<head>',
            '<meta charset="UTF-8">',
            '<title>' . $this->escape($export->cupName) . '</title>',
            '<style>body{font:16px/1.4 sans-serif;margin:2rem;color:#222}table{border-collapse:collapse;width:100%;margin:1rem 0 2rem}th,td{border:1px solid #aaa;padding:.4rem;text-align:left}th{background:#eee}td:nth-child(n+5){text-align:right}@media print{body{margin:0}thead{display:table-header-group}}</style>',
            '</head>',
            '<body>',
            '<h1>' . $this->escape($export->cupName) . '</h1>',
        ];

        foreach ($export->sections as $section) {
            $html[] = $this->section($section);
        }

        $html[] = '</body>';
        $html[] = '</html>';

        return implode("\n", $html) . "\n";
    }
    private function section(ExportCupTableSectionDto $section): string
    {
        $html = [
            '<section>',
            '<h2>' . $this->escape($section->groupName) . '</h2>',
            '<table>',
            '<thead><tr>',
        ];

        foreach (['Место', 'ФИО', 'Год', 'Клуб', 'Очки'] as $label) {
            $html[] = '<th scope="col">' . $label . '</th>';
        }

        foreach ($section->stages as $stage) {
            $html[] = '<th scope="col">' . $this->escape($stage->date) . '</th>';
        }

        $html[] = '</tr></thead>';
        $html[] = '<tbody>';

        foreach ($section->rows as $row) {
            $html[] = $this->row($row, $section->stages);
        }

        $html[] = '</tbody>';
        $html[] = '</table>';
        $html[] = '</section>';

        return implode("\n", $html);
    }

    /** @param list<ExportCupTableStageDto> $stages */
    private function row(ViewCupTableRowDto $row, array $stages): string
    {
        $html = [
            '<tr>',
            '<td>' . $row->place . '</td>',
            '<td>' . $this->escape($row->personName) . '</td>',
            '<td>' . $row->personYear . '</td>',
            '<td>' . $this->escape($row->clubName) . '</td>',
            '<td>' . $this->escape($row->totalPoints) . '</td>',
        ];

        foreach ($stages as $stage) {
            $html[] = '<td>' . $this->escape($row->stages[$stage->stageId]->points ?? '') . '</td>';
        }

        $html[] = '</tr>';

        return implode('', $html);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
