<?php

namespace App\Models\Parser;

use App\Models\Group;
use App\Models\Rank;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class XlsxParser extends AbstractParser
{
    public function parse(string $file, bool $needConvert = true): Collection
    {
        $fileName = tempnam(sys_get_temp_dir(), 'TMP_');
        file_put_contents($fileName, $file);
        $xlsx = new Xlsx();
        $spreadsheet = $xlsx->load($fileName);
        $sheet = $spreadsheet->getActiveSheet();

        $linesList = new Collection();
        $lines = $sheet->toArray();
        $linesCount = count($lines);
        for ($i = 0; $i < $linesCount; $i++) {
            if (empty($lines[$i][0])) {
                continue;
            }

            if (
                $this->groups
                ->filter(fn(string $groupName) => str_contains($lines[$i][0], $groupName))
                ->count() >= 1
            ) {
                $groupNameWithDistance = $lines[$i][0];
                $group = explode(',', $groupNameWithDistance)[0];
                preg_match('#(\d+)\s*КП,\s+(\d+\.\d+) км#msi', $groupNameWithDistance, $linesMatch);
                $distancePoints = (int)$linesMatch[1];
                $distanceLength = floatval($linesMatch[2]) * 1000;
                $groupHeader = [];
                $lines[++$i] = array_filter($lines[$i], fn($item) => $item !== null);
                if (empty($lines[$i])) {
                    $i++;
                    $lines[$i] = array_filter($lines[$i], fn($item) => $item !== null);
                }
                $index = 0;

                foreach ($lines[$i++] as $header) {
                    $header = trim($header);
                    $groupHeader[$index++] = $header;
                }
                for ($n = $i; $n < $linesCount; $n++) {
                    $line = $lines[$n];
                    if (!is_numeric($line[0])) {
                        break;
                    }

                    $protocolLine = [
                        'group' => $group,
                        'distance' => [
                            'length' => $distanceLength,
                            'points' => $distancePoints,
                        ],
                    ];

                    foreach ($groupHeader as $headerIndex => $headerData) {
                        $columnName = $this->getColumn($headerData);
                        if ($columnName === 'name') {
                            $protocolLine['lastname'] = explode(' ', $line[$headerIndex])[0];
                            $protocolLine['firstname'] = explode(' ', $line[$headerIndex])[1];
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
        return str_contains($extension, 'openxmlformats');
    }

    private function getColumn(?string $field): string
    {
        $field = mb_strtolower($field);
        if (str_contains($field, '№')) {
            return 'serial_number';
        } elseif (str_contains($field, 'омер')) {
            return 'runner_number';
        } elseif (str_contains($field, 'амилия')) {
            return 'name';
        } elseif (str_contains($field, '.р.') || $field === 'гр') {
            return 'year';
        } elseif (str_contains($field, 'азряд') || str_contains($field, 'квал')) {
            return 'rank';
        } elseif (str_contains($field, 'оманда') || str_contains($field, 'ллектив')) {
            return 'club';
        } elseif (str_contains($field, 'езультат')) {
            return 'time';
        } elseif (str_contains($field, 'есто')) {
            return 'place';
        } elseif (str_contains($field, 'чки')) {
            return 'points';
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
            case 'points':
            case 'year':
                return $columnData ? (int)$columnData : null;
            case 'club':
                return $columnData;
        }
        return null;
    }
}
