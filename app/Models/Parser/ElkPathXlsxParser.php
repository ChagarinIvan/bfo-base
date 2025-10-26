<?php

declare(strict_types=1);

namespace App\Models\Parser;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use function count;
use function file_put_contents;
use function mb_convert_case;
use function mb_strtolower;
use function str_contains;
use function str_replace;
use function sys_get_temp_dir;
use function tempnam;
use function trim;

class ElkPathXlsxParser extends AbstractParser
{
    public function parse(string $file): Collection
    {
        $linesList = new Collection();
        $lines = $this->getContent($file);
        $linesCount = count($lines);
        $groupHeader = $lines[0];
        $activeDistance = ['length' => 0];

        for ($i = 1; $i < $linesCount; $i++) {
            $protocolLine = [];
            foreach ($groupHeader as $headerIndex => $headerName) {
                if ($headerName) {
                    $columnName = $this->getColumn($headerName);
                    if (empty($columnName)) {
                        continue;
                    }

                    $columnData = $lines[$i][$headerIndex] ?? '';
                    if ($columnName === 'distance') {
                        if ($protocolLine['place'] !== 0) {
                            $protocolLine[$columnName] = ['length' => $this->getValue($columnName, $columnData)];
                            $activeDistance = $protocolLine[$columnName];
                        } else {
                            $protocolLine[$columnName] = $activeDistance;
                        }
                    } elseif ($columnName === 'place' && $columnData === 'п/к') {
                        $protocolLine[$columnName] = null;
                        $protocolLine['vk'] = true;
                        $protocolLine['serial_number'] = -1;
                    } elseif ($columnName === 'place') {
                        $value = $this->getValue($columnName, $columnData);
                        $protocolLine[$columnName] = $value;
                        $protocolLine['serial_number'] = $value;
                    } else {
                        $protocolLine[$columnName] = $this->getValue($columnName, $columnData);
                    }
                }
            }

            $linesList->push($protocolLine);
        }

        return $linesList;
    }

    public function check(string $file, string $extension): bool
    {
        return str_contains($extension, 'openxmlformats') && str_contains('startDate', $this->getContent($file)[0][0] ?? 'not');
    }

    private function getContent(string $file): array
    {
        $fileName = tempnam(sys_get_temp_dir(), 'TMP_');
        file_put_contents($fileName, $file);
        $xlsx = new Xlsx();

        try {
            $spreadsheet = $xlsx->load($fileName);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception) {
            return [];
        }

        return $spreadsheet->getActiveSheet()->toArray();
    }

    private function getColumn(string $field): string
    {
        $field = mb_strtolower($field);
        if ($field === 'classname') {
            return 'group';
        } elseif ($field === 'startnumber') {
            return 'runner_number';
        } elseif ($field === 'place') {
            return 'place';
        } elseif ($field === 'name') {
            return 'firstname';
        } elseif ($field === 'surname') {
            return 'lastname';
        } elseif ($field === 'result') {
            return 'time';
        } elseif ($field === 'club') {
            return 'club';
        } elseif ($field === 'birthyear') {
            return 'year';
        } elseif ($field === 'distance') {
            return 'distance';
        }

        return '';
    }

    private function getValue(string $column, string $columnData): mixed
    {
        $columnData = trim($columnData);
        switch ($column) {
            case 'time':
                try {
                    $time = Carbon::createFromTimeString($columnData);
                } catch (Exception) {
                    $time = null;
                }
                return $time;
            case 'place':
            case 'runner_number':
            case 'year':
                return $columnData ? (int)$columnData : null;
            case 'distance':
                return $columnData ? ((int)$columnData * 1000) : null;
            case 'firstname':
            case 'lastname':
                return mb_convert_case($columnData, MB_CASE_TITLE);
            case 'club':
                return $columnData;
            case 'group':
                return str_replace(['Жанчыны', 'Mужчыны', 'Дзяўчыны', 'Хлопцы'], ['Ж', 'М', 'Ж', 'М'], $columnData);
        }
        return null;
    }
}
