<?php

namespace App\Models\Parser;

use App\Models\Group;
use App\Models\Rank;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class SFRParser extends AbstractParser
{
    public function parse(string $file, bool $needConvert = true): Collection
    {
        $content = $file;
        if ($needConvert) {
            $content = mb_convert_encoding($content, 'utf-8', 'windows-1251');
        }

        $linesList = new Collection();
        preg_match_all('#<h2>(.+?)</h2>.*?<table class=\'rezult\'>(.+?)</table#msi', $content, $nodesMatch);
        foreach ($nodesMatch[2] as $nodeIndex => $node) {
            $text = trim($node);
            $text = trim($text,'-');
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
            $groupName = Group::FIXING_MAP[$groupName] ?? $groupName;

            preg_match_all('#<tr[^>]*>(.+?)</tr>#msi', $text, $linesMatch);
            $linesCount = count($linesMatch[0]);
            if ($linesCount === 0) {
                continue;
            }
            $groupHeader = $linesMatch[1][0];
            preg_match_all('#<th[^>]*>(.*?)</th>#msi', $groupHeader, $headerMatch);
            $groupHeaderData = $headerMatch[1];

            //Ж21, 8,7 км, 14 КП, Контрольное время 120 мин.
            if (preg_match('#(\d+,\d+)\s+[^\d]+,\s+(\d+)\s+[^\d]#s', $nodesMatch[1][$nodeIndex], $match)) {
                $distancePoints = (int)$match[2];
                $distanceLength = floatval(str_replace(',', '.', $match[1])) * 1000;
            }
            for ($index = 1; $index < $linesCount; $index++) {
                $line = trim($linesMatch[1][$index]);
                preg_match_all('#<td[^>]*><nobr>(.*?)</td>#msi', $line, $lineMatch);
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
                    $protocolLine[$columnName] = $this->getValue($columnName, $lineMatch[1][$headerIndex]);
                }
                $linesList->push($protocolLine);
            }
        }

        return $linesList;
    }

    public function check(string $file): bool
    {
        return str_contains($file, "<table class='rezult'>");
    }

    private function getColumn(string $field): string
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
        } elseif (str_contains($field, '.р.')) {
            return 'year';
        } elseif (str_contains($field, 'азр.')) {
            return 'rank';
        } elseif (str_contains($field, 'оманда')) {
            return 'club';
        } elseif (str_contains($field, 'езультат')) {
            return 'time';
        } elseif (str_contains($field, 'есто')) {
            return 'place';
        } elseif (str_contains($field, 'ып.')) {
            return 'complete_rank';
        }
        return '';
    }

    private function getValue(string $column, string $columnData): mixed
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
