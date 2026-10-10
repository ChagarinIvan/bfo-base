<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Cup\ExportCupTableDto;
use App\Application\Dto\Cup\ExportCupTableSectionDto;
use function htmlspecialchars;
use function implode;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

final readonly class CupTableHtmlSerializer
{
    public function __construct(private CupTableExportLayout $layout)
    {
    }

    public function serialize(ExportCupTableDto $export): string
    {
        $html = [
            '<!doctype html>',
            '<body>',
        ];

        $multi = count($export->sections) > 1;
        foreach ($export->sections as $section) {
            $html[] = $this->section($section, $multi);
        }

        $html[] = '</body>';
        $html[] = '</html>';

        return implode("\n", $html) . "\n";
    }

    private function section(ExportCupTableSectionDto $section, bool $withHeader): string
    {
        $html = [
            '<section>',
        ];

        if ($withHeader) {
            $html[] = '<h2>' . $this->escape($section->groupName) . '</h2>';
        }

        $html[] = '<table>';
        $html[] = '<thead><tr>';

        foreach ($this->layout->headings($section) as $label) {
            $html[] = '<th scope="col">' . $this->escape($label) . '</th>';
        }

        $html[] = '</tr></thead>';
        $html[] = '<tbody>';

        foreach ($section->rows as $index => $row) {
            $html[] = '<tr>';
            foreach ($this->layout->values($row, $section, $index + 1) as $column => $value) {
                $stage = $section->stages[$column - 3] ?? null;
                $cell = $stage === null ? null : ($row->stages[$stage->stageId] ?? null);
                $counted = $column >= 3 && $cell?->counted === true;
                $class = $counted ? ' class="cup-table-result--counted"' : '';
                $html[] = '<td' . $class . '>' . $this->escape((string) $value) . '</td>';
            }
            $html[] = '</tr>';
        }

        $html[] = '</tbody>';
        $html[] = '</table>';
        $html[] = '</section>';

        return implode("\n", $html);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
