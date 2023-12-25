<?php
declare(strict_types=1);

namespace App\Models\Parser;

use App\Models\Rank;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use function array_filter;
use function count;
use function explode;
use function file_put_contents;
use function is_numeric;
use function mb_strtolower;
use function preg_match;
use function str_contains;
use function sys_get_temp_dir;
use function tempnam;
use function trim;

class XlsParser extends AbstractParser
{
    public function parse(string $file, bool $needConvert = true): Collection
    {
        $fileName = tempnam(sys_get_temp_dir(), 'TMP_');
        file_put_contents($fileName, $file);
        $xls = new Xls();
        $spreadsheet = $xls->load($fileName);
        $sheet = $spreadsheet->getActiveSheet();

        $linesList = new Collection();
        $lines = $sheet->toArray();
        $linesCount = count($lines);
        for ($i = 0; $i < $linesCount; $i++) {
            if (empty($lines[$i][0])) {
                continue;
            }

            if ($this->groups->containsStrict($lines[$i][0])) {
                $groupName = $lines[$i++][0];
                $distance = $lines[$i++][0];
                $hasDistance = preg_match('#(\d+)\s*КП,\s+(\d+\.\d+) м#msi', $distance, $linesMatch);
                $i = $hasDistance ? $i : $i - 1;
                $distancePoints = $hasDistance ? (int)$linesMatch[1] : 0;
                $distanceLength = $hasDistance ? (float) ($linesMatch[2]) * 1000 : 0;
                $groupHeader = [];
                $lines[$i] = array_filter($lines[$i], static fn ($item) => $item !== null);
                if (empty($lines[$i])) {
                    $i++;
                    $lines[$i] = array_filter($lines[$i], static fn ($item) => $item !== null);
                }
                $index = 0;
                foreach ($lines[$i++] as $header) {
                    $header = trim($header);
                    if (str_contains($header, ' ')) {
                        $headers = explode(' ', $header);
                        $valid = true;
                        foreach ($headers as $header) {
                            $column = $this->getColumn($header);
                            if ($column === '') {
                                $valid = false;
                                break;
                            }
                        }
                        if ($valid) {
                            foreach ($headers as $header) {
                                $groupHeader[$index++] = $header;
                            }
                        }
                    } else {
                        $groupHeader[$index++] = $header;
                    }
                }
                for ($n = $i; $n < $linesCount; $n++) {
                    $line = $lines[$n];
                    if (!is_numeric($line[0])) {
                        break;
                    }

                    $protocolLine = [
                        'group' => $groupName,
                        'distance' => [
                            'length' => $distanceLength,
                            'points' => $distancePoints,
                        ],
                    ];

                    foreach ($groupHeader as $headerIndex => $headerData) {
                        $columnName = $this->getColumn($headerData);
                        if ($columnName === '') {
                            continue;
                        }
                        $protocolLine[$columnName] = $this->getValue($columnName, $line[$headerIndex]);
                    }
                    if (!isset($protocolLine['runner_number'])) {
                        $protocolLine['runner_number'] = $protocolLine['serial_number'];
                    }
                    $linesList->push($protocolLine);
                }
            }
        }

        return $linesList;
    }

    public function check(string $file, string $extension): bool
    {
        return str_contains($extension, 'excel');
    }

    private function getColumn(?string $field): string
    {
        $field = mb_strtolower($field);
        if (str_contains($field, '№')) {
            return 'serial_number';
        } elseif (str_contains($field, 'омер')) {
            return 'runner_number';
        } elseif (str_contains($field, 'амилия')) {
            return 'lastname';
        } elseif (str_contains($field, 'мя')) {
            return 'firstname';
        } elseif (str_contains($field, '.р.') || $field === 'гр') {
            return 'year';
        } elseif (str_contains($field, 'азр.') || str_contains($field, 'квал')) {
            return 'rank';
        } elseif (str_contains($field, 'оманда') || str_contains($field, 'ллектив')) {
            return 'club';
        } elseif (str_contains($field, 'езультат')) {
            return 'time';
        } elseif (str_contains($field, 'есто')) {
            return 'place';
        } elseif (str_contains($field, 'ып.') || $field === 'вып') {
            return 'complete_rank';
        }
        return '';
    }

    private function getValue(string $column, ?string $columnData): mixed
    {
        $columnData = trim($columnData);
        switch ($column) {
            case 'complete_rank':
            case 'rank':
                if (Rank::validateRank($columnData)) {
                    return $columnData;
                }
                break;
            case 'time':
                try {
                    $time = Carbon::createFromTimeString($columnData);
                } catch (Exception) {
                    $time = null;
                }
                return $time;
            case 'place':
                if (is_numeric($columnData)) {
                    return (int)$columnData;
                }
                break;
            case 'runner_number':
            case 'serial_number':
            case 'year':
                return (int)$columnData;
            case 'lastname':
            case 'club':
            case 'firstname':
                return $columnData;
        }
        return null;
    }
}
