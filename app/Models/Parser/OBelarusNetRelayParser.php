<?php

declare(strict_types=1);

namespace App\Models\Parser;

use App\Models\Rank;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function array_key_exists;
use function array_slice;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_numeric;
use function mb_check_encoding;
use function mb_convert_encoding;
use function mb_strtolower;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function preg_split;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strip_tags;
use function strpos;
use function substr;
use function trim;

class OBelarusNetRelayParser extends AbstractParser
{
    private ?int $commandPlace = null;
    private bool $commandVk = false;
    private ?int $commandPoints = null;
    private ?string $commandRank = null;
    private int $commandSerial = 0;

    public function parse(string $file): Collection
    {
        $linesList = new Collection();
        $distancePoints = 0;
        $distanceLength = 0;

        if (mb_check_encoding($file, 'windows-1251')) {
            $file = mb_convert_encoding($file, 'utf-8', 'windows-1251');
        }

        preg_match_all('#<h2>(.+?)</h2>.*?<pre>[^b]*(<b>.+?)</pre>#msi', $file, $nodesMatch);

        foreach ($nodesMatch[2] as $nodeIndex => $node) {
            $this->commandPoints = null;
            $this->commandPlace = null;
            $this->commandRank = null;
            $this->commandSerial = 0;
            $this->commandVk = false;

            $text = trim($node, '-');
            $text = strip_tags($text);
            $text = trim($text);

            if (!str_contains($text, 'амилия')) {
                continue;
            }

            $groupName = $nodesMatch[1][$nodeIndex];
            $groupName = strip_tags($groupName);
            if (preg_match('#(\d+)\s+[^\d]+,\s+((\d+([,.])\d+)\s+[^\d]+|(\d+)\s+[^\d])#s', $groupName, $match)) {
                $distancePoints = (int)$match[1];
                if (str_contains($match[3], ',') || str_contains($match[3], '.')) {
                    if (str_contains($match[3], ',')) {
                        $distanceLength = ((float)str_replace(',', '.', $match[3])) * 1000;
                    } else {
                        $distanceLength = (float)$match[3] * 1000;
                    }
                } else {
                    $distanceLength = (float)$match[3];
                }
            }
            if (str_contains($groupName, ',')) {
                $groupName = substr($groupName, 0, strpos($groupName, ','));
            }
            $groupName = trim($groupName, '+');

            $lines = preg_split('/\n|\r\n?/', $text);
            $linesCount = count($lines);
            if ($linesCount < 3) {
                continue;
            }
            $groupHeader = $lines[0];
            $groupHeader = trim($groupHeader);
            $groupHeader = preg_replace('#\s+#', ' ', $groupHeader);
            $headers = explode(' ', $groupHeader);
            $groupHeaderIndex = count($headers) - 1;
            $isRelay = str_contains($text, 'на этапе') || str_contains($text, 'команды') || str_contains($text, 'Ком. рез-т');
            $isOpen = !$isRelay || str_contains($groupName, 'PEN') || str_contains($groupName, 'pen');

            for ($index = 1; $index < $linesCount; $index++) {
                $line = trim($lines[$index]);
                if (empty($line)) {
                    continue;
                }
                if (str_contains($line, 'ласс дистан') || str_contains($line, 'лавный судь')) {
                    break;
                }
                if (!preg_match('#\d#', $line)) {
                    continue;
                }
                if (is_numeric($line) || $isOpen) {
                    $this->commandSerial = (int) $line;
                    $this->commandPoints = null;
                    $this->commandPlace = null;
                    $this->commandRank = null;
                    $this->commandVk = false;

                    if (!$isOpen || is_numeric($line)) {
                        continue;
                    }
                } elseif (preg_match('#^(\d+)\s+(\d+|-|в/к)\s+([^\d\s]+|-)+\s+(\d+|-)#u', $line, $match)) {
                    $this->commandSerial = (int)$match[1];
                    $this->commandPoints = is_numeric($match[4]) ? (int)$match[4] : null;
                    $this->commandPlace = is_numeric($match[2]) ? (int)$match[2] : null;
                    if ($match[2] === 'в/к') {
                        $this->commandVk = true;
                    }
                    $this->commandRank = $match[3];
                    continue;
                }

                $preparedLine = preg_replace('#=#', ' ', $line);
                $preparedLine = preg_replace('#\s+#', ' ', $preparedLine);
                $lineData = explode(' ', $preparedLine);
                $fieldsCount = count($lineData);

                $protocolLine = [
                    'group' => $groupName,
                    'distance' => [
                        'length' => $distanceLength,
                        'points' => $distancePoints,
                    ],
                ];

                $indent = 1;

                $number = false;
                for ($i = $groupHeaderIndex; $i > 2; $i--) {
                    $columnName = $this->getColumn($headers[$i]);
                    if ($columnName === 'runner_number') {
                        $number = true;
                    }

                    if ($columnName === '') {
                        break;
                    }
                    if ($columnName === 'time' && array_key_exists('time', $protocolLine)) {
                        continue;
                    }

                    $protocolLine = $this->getValue($columnName, $lineData, $fieldsCount, $indent, $protocolLine);
                }

                $protocolLine['points'] = $this->commandPoints;
                $protocolLine['place'] = $this->commandPlace;
                $protocolLine['complete_rank'] = $this->commandRank;
                $protocolLine['vk'] = $protocolLine['vk'] ?? $this->commandVk;

                for ($nameIndex = 0; $nameIndex <= $fieldsCount - $indent; $nameIndex++) {
                    $value = $lineData[$nameIndex];
                    if (!is_numeric($value)) {
                        break;
                    }
                }

                if (!$number) {
                    $protocolLine['runner_number'] = $lineData[$nameIndex - 1];
                }
                $protocolLine['lastname'] = $lineData[$nameIndex++];
                $protocolLine['firstname'] = $lineData[$nameIndex++];

                $protocolLine['serial_number'] = $isOpen ? $lineData[0] : $this->commandSerial;
                $protocolLine['club'] = implode(' ', array_slice($lineData, $nameIndex, $fieldsCount - $indent - $nameIndex + 1));
                $linesList->push($protocolLine);
            }
        }

        return $linesList;
    }

    public function check(string $file, string $extension): bool
    {
        if (str_contains($extension, 'htm')) {
            return (bool)preg_match('#<b>\d[^<]*[^\d^\s]#', $file);
        }

        return false;
    }

    private function getColumn(string $field): string
    {
        $field = mb_strtolower($field);
        if (str_contains($field, 'чки')) {
            return 'points';
        }
        if (str_contains($field, 'ып')) {
            return 'complete_rank';
        }
        if (str_contains($field, 'есто')) {
            return 'place';
        }
        if (str_contains($field, 'ремя') || str_contains($field, 'рез') || $field === 'ком.') {
            return 'time';
        }
        if ($field === 'гр' || $field === 'г.р.') {
            return 'year';
        }
        if (str_contains($field, 'омер')) {
            return 'runner_number';
        }
        if (str_contains($field, 'вал') || str_contains($field, 'азряд')) {
            return 'rank';
        }
        if (str_contains($field, 'рим')) {
            return 'info';
        }
        return '';
    }

    private function getValue(string $column, array $lineData, int $fieldsCount, int &$indent, array $protocolLine): array
    {
        if ($column === 'place') {
            $place = $lineData[$fieldsCount - $indent];
            if ($place === 'в/к' || $place === 'лично') {
                $indent++;
                $protocolLine['vk'] = true;
                return $protocolLine;
            }
            if ($place === '-') {
                $indent++;
                $protocolLine['place'] = null;
                return $protocolLine;
            }
            if (
                preg_match('#^\d+$#', $place) && preg_match('#\d\d:\d\d:\d\d#', implode('', $lineData))
                && !preg_match('/^19\d{2}|2\d{3}$/', $column)
            ) {
                $indent++;
                $this->commandPlace = (int)$place;
            }
        } elseif ($column === 'points') {
            $column = $lineData[$fieldsCount - $indent];
            if (
                is_numeric($column) && $this->commandPoints === null && $this->commandSerial != $column
                && !preg_match('/^19\d{2}|2\d{3}$/', $column)
            ) {
                $indent++;
                $this->commandPoints = (int)$column;
            } elseif ($column === '-') {
                $indent++;
                $this->commandPoints = null;
            }
        } elseif ($column === 'runner_number') {
            $column = $lineData[$fieldsCount - $indent];
            if (is_numeric($column)) {
                $indent++;
                $protocolLine['runner_number'] = $column;
            }
        } elseif ($column === 'complete_rank') {
            $column = $lineData[$fieldsCount - $indent];
            if ((Rank::validateRank($column) || $column === '-') && !in_array($column, ['1', '2', '3'], true)) {
                $indent++;
                $this->commandRank = $column;
            }
        } elseif ($column === 'info') {
            $column = $lineData[$fieldsCount - $indent];
            if ($column === 'в/к') {
                $indent++;
                $protocolLine['vk'] = true;
            }
        } elseif ($column === 'time') {
            $column1 = $lineData[$fieldsCount - $indent];
            $column2 = $lineData[$fieldsCount - $indent - 1];
            if (preg_match('#\d\d:\d\d:\d\d#', $column1) && preg_match('#\d\d:\d\d:\d\d#', $column2)) {
                $indent += 2;
                $timeColumn = $column2;
            } elseif (preg_match('#\d\d:\d\d:\d\d#', $column1) && !preg_match('#\d\d:\d\d:\d\d#', $column2)) {
                $indent++;
                $timeColumn = $column1;
            } elseif (str_starts_with($column1, '20.10') || $column1 === '24.4' || $column1 === 'DSQ' || $column1 === 'пп.20.10') {
                $column3 = $lineData[$fieldsCount - $indent - 2];
                if (preg_match('#\d\d:\d\d:\d\d#', $column3)) {
                    $indent += 3;
                    $timeColumn = $column3;
                } elseif (str_starts_with($column1, '20.10') || $column1 === '24.4') {
                    $indent += 2;
                    $protocolLine['time'] = null;
                    return $protocolLine;
                } else {
                    $indent += 1;
                    $protocolLine['time'] = null;
                    return $protocolLine;
                }
            } elseif ($column1 === 'старт' && $column2 === 'не') {
                $indent += 2;
                $protocolLine['time'] = null;
                return $protocolLine;
            } else {
                $protocolLine['time'] = null;
                return $protocolLine;
            }
            $protocolLine['time'] = Carbon::createFromTimeString($timeColumn);
        } elseif ($column === 'rank') {
            $rank = $lineData[$fieldsCount - $indent];
            if (Rank::validateRank($rank)) {
                $indent++;
                $protocolLine['rank'] = $rank;
            }
        } elseif ($column === 'year') {
            $year = $lineData[$fieldsCount - $indent];
            if (is_numeric($year)) {
                $indent++;
                $protocolLine['year'] = $year;
            }
        }

        return $protocolLine;
    }
}
