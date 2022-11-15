<?php

namespace App\Models\Parser;

use App\Models\Rank;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OBelarusSpanParser extends AbstractParser
{
    public function parse(string $file, bool $needConvert = true): Collection
    {
        if ($needConvert) {
            $file = mb_convert_encoding($file, 'utf-8', 'windows-1251');
        }
        $linesList = new Collection();
        $distancePoints = 0;
        $distanceLength = 0;

        preg_match_all('#<h2[^>]*>(.+?)</h2>.*?<pre>(.+?)</pre#msi', $file, $nodesMatch);
        foreach ($nodesMatch[2] as $nodeIndex => $node) {
            $text = trim($node, '-');
            $text = strip_tags($text);
            $text = trim($text);
            if (!str_contains($text, 'амилия')) {
                continue;
            }

            $groupName = $nodesMatch[1][$nodeIndex];
            $groupName = strip_tags($groupName);
            if (preg_match('#(\d+)\s+[^\d]+,\s+(\d+\.?\d*?)\s+[^\d]#s', $groupName, $match)) {
                $distancePoints = (int)$match[1];
                $distanceLength = $match[2] * 1000;
            }

            if (str_contains($groupName, ',')) {
                $groupName = substr($groupName, 0, strpos($groupName, ','));
            }
            $groupName = trim($groupName, '+');

            $lines = preg_split('/\n|\r\n?/', $text);
            $linesCount = count($lines);
            $startIndex = 1;

            if ($linesCount < 3) {
                continue;
            }

            $groupHeader = $lines[0];
            if (empty(trim($groupHeader, '-'))) {
                $groupHeader = $lines[1];
                $startIndex = 3;
            }

            $groupHeader = trim($groupHeader);
            $groupHeader = preg_split('#\s+#', $groupHeader);
            $groupHeaderIndex = count($groupHeader) - 1;

            for ($index = $startIndex; $index < $linesCount; $index++) {
                $line = trim($lines[$index]);
                if (empty(trim($line, '-'))) {
                    continue;
                }
                if (empty($line)) {
                    continue;
                }

                if (str_contains($line, 'ласс дистан') || str_contains($line, 'лавный судь') || str_contains($line, 'нг не опреде')) {
                    break;
                }

                $preparedLine = preg_replace('#=#', ' ', $line);
                $lineData = preg_split('#\s+#', $preparedLine);
                $fieldsCount = count($lineData);

                $protocolLine = [
                    'group' => $groupName,
                    'distance' => [
                        'length' => $distanceLength,
                        'points' => $distancePoints,
                    ],
                ];

                $indent = 1;

                for ($i = $groupHeaderIndex; $i > 2; $i--) {
                    $columnName = $this->getColumn($groupHeader[$i]);
                    if (str_contains($groupHeader[$i], 'тставани')) {
                        $indent++;
                        continue;
                    }
                    if ($columnName === '') {
                        break;
                    }
                    if ($columnName === 'time' && array_key_exists('time', $protocolLine)) {
                        continue;
                    }
                    $protocolLine = $this->getValue($columnName, $lineData, $fieldsCount, $indent, $protocolLine);
                }

                $protocolLine['serial_number'] = $lineData[0];
                $protocolLine['lastname'] = $lineData[1];
                $protocolLine['firstname'] = $lineData[2];
                $protocolLine['club'] = implode(' ', array_slice($lineData, 3, $fieldsCount - $indent - 2));
                $linesList->push($protocolLine);
            }
        }

        return $linesList;
    }

    public function check(string $file, string $extension): bool
    {
        if ($extension === 'html') {
            //этот паттерн срабатывает на ОбеларусЭстафеты..поэтому он позже в проверказ парсеров
            return (bool)preg_match('#<span\sid="m\d\d?"></span>[^<]+,#', $file);
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
        if ($field ==='гр' || $field === 'г.р.') {
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
        if ($column === 'info') {
            return $protocolLine;
        }
        if ($column === 'place') {
            $place = $lineData[$fieldsCount - $indent];
            if ($place === 'в/к' || $place === 'лично') {
                $indent++;
                $protocolLine['vk'] = true;
                $protocolLine['place'] = null;
                return $protocolLine;
            } elseif ($place === '-') {
                $indent++;
                $protocolLine['place'] = null;
                return $protocolLine;
            } elseif (str_contains($place, 'ДИСКВ') || $place === 'н.старт' || $place === 'снят' || $place === 'кв') {
                $protocolLine['place'] = null;
                return $protocolLine;
            } elseif (preg_match('#^\d+$#', $place) && preg_match('#\d\d:\d\d:\d\d#', implode('', $lineData))) {
                $indent++;
                $protocolLine['place'] = $place;
                return $protocolLine;
            } else {
                $protocolLine['place'] = null;
            }
        } elseif ($column === 'complete_rank') {
            $rank = $lineData[$fieldsCount - $indent];
            if (Rank::validateRank($rank)) {
                $indent++;
                $protocolLine['complete_rank'] = $rank;
                return $protocolLine;
            } elseif ($rank === '-') {
                $indent++;
                $protocolLine['complete_rank'] = null;
                return $protocolLine;
            }
        } elseif ($column === 'points') {
            $column = $lineData[$fieldsCount - $indent];
            if (is_numeric($column)) {
                $indent++;
                $protocolLine['points'] = (int)$column;
                return $protocolLine;
            } elseif ($column === '-') {
                $indent++;
                $protocolLine['points'] = null;
                return $protocolLine;
            }
        } elseif ($column === 'runner_number') {
            $column = $lineData[$fieldsCount - $indent];
            if (is_numeric($column)) {
                $indent++;
                $protocolLine['runner_number'] = $column;
            }
        } elseif ($column === 'time') {
            $column1 = $lineData[$fieldsCount - $indent];
            $column2 = $lineData[$fieldsCount - $indent - 1];
            if (preg_match('#\d\d:\d\d:\d\d#', $column1) && !preg_match('#\d\d:\d\d:\d\d#', $column2)) {
                $indent++;
                $timeColumn = $column1;
            } else {
                $protocolLine['time'] = null;
                if (
                    str_contains($column1, 'ДИСКВ')
                    || $column1 === 'н.старт'
                    || $column1 === 'снят'
                    || $column1 === 'Сошел'
                ) {
                    $indent++;
                } elseif (
                    ($column1 === 'кв' && $column2 === 'снят')
                    || ($column1 === '20.10' && $column2 === 'пп')
                ) {
                    $indent += 2;
                }
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
            if (is_numeric($year) && preg_match('#\d{4}#', $year)) {
                $indent++;
                $protocolLine['year'] = $year;
            } else {
                $protocolLine['year'] = null;
            }
        }
        return $protocolLine;
    }
}
