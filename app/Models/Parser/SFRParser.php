<?php

declare(strict_types=1);

namespace App\Models\Parser;

use App\Domain\Rank\Rank;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function count;
use function explode;
use function is_numeric;
use function mb_convert_encoding;
use function mb_strtolower;
use function preg_match;
use function preg_match_all;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function strip_tags;
use function strpos;
use function substr;
use function trim;

class SFRParser extends AbstractParser
{
    public function parse(string $file): Collection
    {
        $content = $file;
        $content = mb_convert_encoding($content, 'utf-8', 'windows-1251');

        $linesList = new Collection();
        preg_match_all('#<h2>(.+?)</h2>.*?<table\s+class=.rezult.>(.+?)</table#msi', $content, $nodesMatch);
        foreach ($nodesMatch[2] as $nodeIndex => $node) {
            $text = trim($node);
            $text = trim($text, '-');
            $text = trim($text);
            if (!str_contains($text, 'амилия')) {
                continue;
            }
            $distanceLength = 0;
            $distancePoints = 0;
            $groupName = $nodesMatch[1][$nodeIndex];
            $groupName = strip_tags($groupName);
            if (str_contains($groupName, ',')) {
                $groupName = substr($groupName, 0, strpos($groupName, ','));
            }

            preg_match_all('#<tr[^>]*>(.+?)</tr>#msi', $text, $linesMatch);
            $linesCount = count($linesMatch[0]);
            if ($linesCount === 0) {
                continue;
            }
            $groupHeader = $linesMatch[1][0];
            preg_match_all('#<th[^>]*>(.*?)</th>#msi', $groupHeader, $headerMatch);
            $groupHeaderData = $headerMatch[1];

            //Ж21, 8,7 км, 14 КП, Контрольное время 120 мин.
            if (preg_match('#(\d+,\d+)\s+\D+,\s+(\d+)\s+\D#', $nodesMatch[1][$nodeIndex], $match)) {
                $distancePoints = (int)$match[2];
                $distanceLength = (float)str_replace(',', '.', $match[1]) * 1000;
            }

            //Ж10, 7КП, 0,9 км, контрольное время 75 мин.
            if (preg_match('#(\d+)\D+?,\s+(\d+,\d+)\s+\D+#', $nodesMatch[1][$nodeIndex], $match)) {
                $distancePoints = (int)$match[1];
                $distanceLength = (float)str_replace(',', '.', $match[2]) * 1000;
            }

            for ($index = 1; $index < $linesCount; $index++) {
                $skip = false;
                $line = trim($linesMatch[1][$index]);
                preg_match_all('#<td[^>]*>(?:<nobr>)?(.*?)(?:<nobr>)?</td>#msi', $line, $lineMatch);
                $protocolLine = [
                    'group' => $groupName,
                    'distance' => [
                        'length' => $distanceLength,
                        'points' => $distancePoints,
                    ],
                ];

                foreach ($groupHeaderData as $headerIndex => $headerData) {
                    $columnName = $this->getColumn($headerData);
                    if ($columnName === '') {
                        continue;
                    }
                    $value = $lineMatch[1][$headerIndex];
                    if (str_ends_with($value, '</nobr>')) {
                        $value = substr($value, 0, -7);
                    }
                    $protocolLine[$columnName] = $this->getValue($columnName, $value);
                    if ($columnName === 'lastname') {
                        if (empty($protocolLine[$columnName])) {
                            $skip = true;
                            break;
                        }
                        if (str_contains($headerData, 'амилия, Имя')) {
                            [$protocolLine['lastname'], $protocolLine['firstname']] = explode(' ', $protocolLine[$columnName]);
                        }
                    }
                }
                if (!$skip) {
                    $linesList->push($protocolLine);
                }
            }
        }

        return $linesList;
    }

    public function check(string $file, string $extension): bool
    {
        if (str_contains($extension, 'htm')) {
            return str_contains($file, '<table class="rezult">')
                || str_contains($file, '<table class=\'rezult\'>')
            ;
        }

        return false;
    }

    private function getColumn(string $field): string
    {
        $field = mb_strtolower($field);
        if (str_contains($field, '№')) {
            return 'serial_number';
        }

        if (str_contains($field, 'омер')) {
            return 'runner_number';
        }

        if (str_contains($field, 'амилия')) {
            return 'lastname';
        }

        if (str_contains($field, 'мя')) {
            return 'firstname';
        }

        if (str_contains($field, '.р.')) {
            return 'year';
        }

        if (str_contains($field, 'ып.') && str_contains($field, 'азр.')) {
            return 'complete_rank';
        }

        if (str_contains($field, 'азр.')) {
            return 'rank';
        }

        if (str_contains($field, 'оманда')) {
            return 'club';
        }

        if (str_contains($field, 'езультат')) {
            return 'time';
        }

        if (str_contains($field, 'есто')) {
            return 'place';
        }

        if (str_contains($field, 'чки')) {
            return 'points';
        }

        return '';
    }

    private function getValue(string $column, string $columnData): string|int|null|Carbon
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
            case 'points':
                return (int)$columnData;
            case 'lastname':
            case 'club':
            case 'firstname':
                return $columnData;
        }
        return null;
    }
}
