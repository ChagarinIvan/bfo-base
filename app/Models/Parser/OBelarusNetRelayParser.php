<?php

namespace App\Models\Parser;

use App\Exceptions\ParsingException;
use App\Models\Group;
use App\Models\Rank;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class OBelarusNetRelayParser implements ParserInterface
{
    private ?int $commandPlace = null;
    private ?int $commandPoints = null;
    private ?string $commandRank = null;
    private int $commandCounter = 1;
    private int $commandSerial;

    public function parse(string $file, bool $needConvert = true): Collection
    {
        try {
            $doc = new DOMDocument();
            @$doc->loadHTML($file);
            $xpath = new DOMXpath($doc);
            $preNodes = $xpath->query('//pre');
            $linesList = new Collection();
            $distancePoints = 0;
            $distanceLength = 0;

            foreach ($preNodes as $node) {
                $this->commandCounter = 1;
                $this->commandPoints = null;
                $this->commandPlace = null;
                $this->commandRank = null;

                $text = trim($node->nodeValue);
                $text = trim($text, '-');
                $text = trim($text);
                if (!str_contains($text, 'амилия')) {
                    continue;
                }
                $groupNode = $xpath->query('preceding::h2[1]', $node);
                $groupName = $groupNode[0]->nodeValue;
                if (preg_match('#(\d+)\s+[^\d]+,\s+((\d+([,.])\d+)\s+[^\d]+|(\d+)\s+[^\d])#s', $groupName, $match)) {
                    $distancePoints = (int)$match[1];
                    if (str_contains($match[3], ',') || str_contains($match[3], '.')) {
                        if (str_contains($match[3], ',')) {
                            $distanceLength = floatval(str_replace(',', '.', $match[3])) * 1000;
                        } else {
                            $distanceLength = (float)$match[3];
                        }
                    } else {
                        $distanceLength = floatval($match[4]);
                    }
                }
                if (str_contains($groupName, ',')) {
                    $groupName = substr($groupName, 0, strpos($groupName, ','));
                }
                $groupName = trim($groupName, '+');
                $groupName = Group::FIXING_MAP[$groupName] ?? $groupName;

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
                $isOpen = str_contains($groupName, 'OPEN');

                for ($index = 1; $index < $linesCount; $index++) {
                    $line = trim($lines[$index]);
                    if (empty($line)) {
                        continue;
                    }
                    if (str_contains($line, 'ласс дистан')) {
                        break;
                    }
                    if (!preg_match('#\d#', $line)) {
                        continue;
                    }
                    if (is_numeric($line) || $isOpen) {
                        $this->commandCounter = 1;
                        $this->commandSerial = (int)$line;
                        $this->commandPoints = null;
                        $this->commandPlace = null;
                        $this->commandRank = null;
                        if (!$isOpen) {
                            continue;
                        }
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

                    $nameIndex = $isOpen ? 1 : 0;
                    if (!$number) {
                        $nameIndex++;
                    }
                    $protocolLine['lastname'] = $lineData[$nameIndex++];
                    $protocolLine['firstname'] = $lineData[$nameIndex++];
                    if ($number === false) {
                        $protocolLine['runner_number'] = $lineData[$isOpen ? 1 : 0];
                    }

                    $protocolLine['serial_number'] = $isOpen ? $lineData[0] : $this->commandSerial;
                    $protocolLine['club'] = implode(' ', array_slice($lineData, $nameIndex, $fieldsCount - $indent - $nameIndex + 1));
                    $linesList->push($protocolLine);
                    $this->commandCounter++;
                }
            }
            return $linesList;
        } catch (Exception $e) {
            throw new ParsingException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    public function check(string $file): bool
    {
        return (bool)preg_match('#<b>\d[^<]*[^\d^\s]#', $file);
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
        if ($column === 'place') {
            $place = $lineData[$fieldsCount - $indent];
            if ($place === 'в/к') {
                $indent++;
                $protocolLine['vk'] = true;
                return $protocolLine;
            }
            if ($place === '-') {
                $indent++;
                $protocolLine['place'] = null;
                return $protocolLine;
            }
            if (preg_match('#^\d+$#', $place) && preg_match('#\d\d:\d\d:\d\d#', implode('', $lineData))) {
                $indent++;
                $this->commandPlace = (int)$place;
            }
        } elseif ($column === 'points') {
            $column = $lineData[$fieldsCount - $indent];
            if (is_numeric($column) && $this->commandPoints === null) {
                $indent++;
                $this->commandPoints = (int)$column;
            }
        } elseif ($column === 'runner_number') {
            $column = $lineData[$fieldsCount - $indent];
            if (is_numeric($column)) {
                $indent++;
                $protocolLine['runner_number'] = $column;
            }
        } elseif ($column === 'complete_rank') {
            $column = $lineData[$fieldsCount - $indent];
            if (Rank::validateRank($column) || $column === '-') {
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
            } elseif ($column1 === '20.10') {
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
