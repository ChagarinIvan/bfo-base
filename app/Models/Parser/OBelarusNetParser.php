<?php

declare(strict_types=1);

namespace App\Models\Parser;

use App\Domain\Rank\Rank;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function array_slice;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_numeric;
use function mb_convert_encoding;
use function mb_strtolower;
use function preg_match;
use function preg_replace;
use function preg_split;
use function str_contains;
use function str_replace;
use function strpos;
use function substr;
use function trim;

class OBelarusNetParser extends AbstractParser
{
    public function parse(string $file): Collection
    {
        $doc = new DOMDocument();
        $content = $file;
        $content = str_replace(["&nbsp;", " "], ' ', $content);
        @$doc->loadHTML($content);
        $xpath = new DOMXpath($doc);
        $preNodes = $xpath->query('//pre');
        $linesList = new Collection();
        foreach ($preNodes as $node) {
            $text = trim($node->nodeValue);
            $text = trim($text, '-');
            $text = mb_convert_encoding($text, 'iso-8859-1', 'utf-8');
            $text = trim($text);
            if (!str_contains($text, 'амилия')) {
                continue;
            }
            $distanceLength = 0;
            $distancePoints = 0;
            $groupNode = $xpath->query('preceding::h2[1]', $node);
            $groupName = $groupNode[0]->nodeValue;
            $groupName = mb_convert_encoding($groupName, 'iso-8859-1', 'utf-8');
            if (str_contains($groupName, ',')) {
                $groupName = substr($groupName, 0, strpos($groupName, ','));
            }

            $lines = preg_split('/\n|\r\n?/', $text);

            $linesCount = count($lines);
            if ($linesCount < 3) {
                continue;
            }
            $groupHeader = $lines[2];
            if (empty(trim($groupHeader, '-'))) {
                $groupHeader = $lines[3];
            }
            $groupHeader = preg_replace('#\s+#u', ' ', $groupHeader);

            $groupHeaderData = explode(' ', $groupHeader);
            $groupHeaderIndex = count($groupHeaderData) - 1;

            if (preg_match('#(\d+)\s+[^\d]+,\s+((\d+,\d+)\s+[^\d]+|(\d+)\s+[^\d])#s', $lines[0], $match)) {
                $distancePoints = (int)$match[1];
                if (str_contains($match[2], ',')) {
                    $distanceLength = (float)str_replace(',', '.', $match[3]) * 1000;
                } else {
                    $distanceLength = (float)$match[3];
                }
            }

            for ($index = 4; $index < $linesCount; $index++) {
                $line = trim($lines[$index]);

                if (empty(trim($line, '-'))) {
                    if ($index > 4) {
                        break;
                    }

                    continue;
                }
                $preparedLine = preg_replace('#=#u', ' ', $line);
                $preparedLine = preg_replace('#\s+#u', ' ', $preparedLine);
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
                for ($i = $groupHeaderIndex; $i > 2; $i--) {
                    $columnName = $this->getColumn($groupHeaderData[$i]);
                    if ($columnName === '') {
                        if (in_array($groupHeaderData[$i], ['КП', 'Финиш'], true)) {
                            $value = $lineData[$fieldsCount - $indent];

                            if (!str_contains($value, 'п.п.')) {
                                $indent++;
                            }
                        }
                        continue;
                    }
                    $protocolLine[$columnName] = $this->getValue($columnName, $lineData, $fieldsCount, $indent, $protocolLine);
                }
                $protocolLine['serial_number'] = (int)$lineData[0];
                $protocolLine['lastname'] = $lineData[1];
                $protocolLine['firstname'] = $lineData[2];
                $protocolLine['club'] = implode(' ', array_slice($lineData, 3, $fieldsCount - $indent - 2));

                $linesList->push($protocolLine);
            }
        }

        //        dd($linesList);
        return $linesList;
    }

    public function check(string $file, string $extension): bool
    {
        return true;
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
        if (str_contains($field, 'зультат') || str_contains($field, 'ремя')) {
            return 'time';
        }
        if ($field === 'гр' || $field === 'г.р.') {
            return 'year';
        }
        if (str_contains($field, 'омер')) {
            return 'runner_number';
        }
        if (str_contains($field, 'вал')) {
            return 'rank';
        }
        if (str_contains($field, 'рим') || str_contains($field, 'тарт')) {
            return 'prim';
        }
        return '';
    }

    private function getValue(string $column, array $lineData, int $fieldsCount, int &$indent, array &$data): mixed
    {
        if ($column === 'points') {
            $points = $lineData[$fieldsCount - $indent++];
            if ($points === 'лично') {
                $points = $lineData[$fieldsCount - $indent++];
            }
            return is_numeric($points) ? (int)$points : null;
        }
        if ($column === 'complete_rank') {
            return $lineData[$fieldsCount - $indent++];
        }
        if ($column === 'place') {
            $place = $lineData[$fieldsCount - $indent];
            if ($place === 'в/к') {
                $data['vk'] = true;
                $indent++;
                return null;
            }
            if (is_numeric($place)) {
                $indent++;
                return  (int)$place;
            }
            if ($place === '-') {
                $indent++;
            }
            return null;
        }
        if ($column === 'time') {
            $time = $lineData[$fieldsCount - $indent++];
            if (preg_match('#\w\.\w\.\d\d\.\d\d#u', $time)) {
                return null;
            }
            if (preg_match('#\d\d\.\d{1,2}#', $time)) {
                $indent++;
                return null;
            }
            try {
                $time = Carbon::createFromTimeString($time);
            } catch (Exception) {
                $time = null;
            }
            return $time;
        }
        if ($column === 'runner_number') {
            return (int)$lineData[$fieldsCount - $indent++];
        }
        if ($column === 'rank') {
            $rank = $lineData[$fieldsCount - $indent];
            if (Rank::validateRank($rank)) {
                $indent++;
                return $rank;
            }
        }
        if ($column === 'year') {
            $year = $lineData[$fieldsCount - $indent];
            if (is_numeric($year)) {
                $indent++;
                return (int)$year;
            }

            return null;
        }
        if ($column === 'prim') {
            $prim = $lineData[$fieldsCount - $indent];
            if (preg_match('#\d\d:\d\d#', $prim)) {
                $indent++;
            }
        }
        return null;
    }
}
