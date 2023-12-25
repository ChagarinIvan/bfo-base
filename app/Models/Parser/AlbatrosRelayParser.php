<?php
declare(strict_types=1);

namespace App\Models\Parser;

use App\Models\Rank;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function array_slice;
use function count;
use function explode;
use function implode;
use function is_numeric;
use function mb_strtolower;
use function preg_match;
use function preg_replace;
use function preg_split;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strpos;
use function substr;
use function trim;

class AlbatrosRelayParser extends AbstractParser
{
    public function parse(string $file, bool $needConvert = true): Collection
    {
        $doc = new DOMDocument();
        if (!str_starts_with($file, '<html lang="ru">')) {
            $file = '<?xml encoding="UTF-8">' . $file . '</xml>';
        }
        @$doc->loadHTML($file);
        $xpath = new DOMXpath($doc);
        $preNodes = $xpath->query('//pre');
        $linesList = new Collection();

        foreach ($preNodes as $node) {
            $text = trim($node->nodeValue);
            $text = trim($text, '-');
            $text = trim($text);

            $groupNode = $xpath->query('preceding::h2[1]', $node);
            $groupName = $groupNode[0]->nodeValue;

            if (str_contains($groupName, ',')) {
                $groupName = substr($groupName, 0, strpos($groupName, ','));
            }

            $lines = preg_split('/\n|\r\n?/', $text);
            $linesCount = count($lines);
            $distance = $lines[0];
            $distanceLength = 0;
            $distancePoints = 0;
            if (preg_match('#(\d+)\s+[^\d]+,\s+((\d+,\d+)\s+[^\d]+|(\d+)\s+[^\d])#s', $distance, $match)) {
                $distancePoints = (int)$match[1];
                if (str_contains($match[2], ',')) {
                    $distanceLength = (float) (str_replace(',', '.', $match[3])) * 1000;
                } else {
                    $distanceLength = (float) ($match[4]);
                }
            } elseif (count($lines) < 4) {
                continue;
            }
            $withLines = false;
            $groupHeader = $lines[2];
            if (!str_contains($groupHeader, 'Фамилия')) {
                $withLines = true;
                $groupHeader = $lines[0];
            }
            $groupHeaderLine = preg_replace('#\s+#', ' ', $groupHeader);
            $groupHeaderLine = trim($groupHeaderLine);
            $groupHeaderData = explode(' ', $groupHeaderLine);
            $groupHeaderIndex = count($groupHeaderData) - 1;
            $lastProtocolLine = [];

            for ($index = ($withLines ? 2 : 5) ; $index < $linesCount; $index++) {
                $line = trim($lines[$index]);

                if (empty(trim($line, '-'))) {
                    break;
                }
                $preparedLine = preg_replace('#\s+#', ' ', $line);
                $lineData = explode(' ', $preparedLine);

                if (isset($lineData[1]) && $lineData[1] === '0') {
                    continue;
                }

                $fieldsCount = count($lineData);
                if ($fieldsCount <= 3 && is_numeric($lineData[0])) {
                    $commandCounter = 0;
                    if (isset($lineData[1])) {
                        $commandPoints = is_numeric($lineData[1]) ? (int)$lineData[1] : null;
                    } else {
                        $commandPoints = 0;
                    }

                    $commandPlace = (int)$lineData[0];
                    if (isset($lineData[2])) {
                        $commandRank = $lineData[2] !== '-' ? $lineData[2] : null;
                    } else {
                        $commandRank = null;
                    }
                    continue;
                } else {
                    if (!empty($lastProtocolLine)) {
                        $unClubedLine = str_replace([$lastProtocolLine['club'], 'не старт'], '', $line);
                        $unClubedLine = preg_replace('#\s+#', ' ', $unClubedLine);
                        $unClubedLine = trim($unClubedLine);
                        $unClubedLineData = explode(' ', $unClubedLine);
                        if (count($unClubedLineData) === 1) {
                            continue;
                        }
                    }
                }

                $protocolLine = [
                    'group' => $groupName,
                    'complete_rank' => $commandRank,
                    'place' => $commandPlace,
                    'points' => $commandPoints,
                    'distance' => [
                        'length' => $distanceLength,
                        'points' => $distancePoints,
                    ],
                ];
                $indent = 1;

                for ($i = $groupHeaderIndex; $i > 2; $i--) {
                    $columnName = $this->getColumn($groupHeaderData[$i]);
                    if ($columnName === '') {
                        break;
                    }
                    if ($columnName === 'time' && isset($protocolLine['time'])) {
                        continue;
                    }
                    $protocolLine = $this->getValue($columnName, $lineData, $fieldsCount, $indent, $protocolLine);
                }

                $protocolLine['serial_number'] = (int)$lineData[0];
                $protocolLine['runner_number'] = (int)$lineData[0];
                $protocolLine['lastname'] = $lineData[1];
                $protocolLine['firstname'] = $lineData[2];
                $protocolLine['club'] = implode(' ', array_slice($lineData, 3, $fieldsCount - $indent - 2));

                $linesList->push($protocolLine);
                $lastProtocolLine = $protocolLine;
                $commandCounter++;
            }
        }

        return $linesList;
    }

    public function check(string $file, string $extension): bool
    {
        if ($extension === 'html') {
            return preg_match('#<b>\d+\s+(-|\d+|в/к)\s+(-|[^\s]{1,3})<#', $file);
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
        if (str_contains($field, 'ремя')) {
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
        if (str_contains($field, 'рим')) {
            return 'info';
        }
        return '';
    }

    private function getValue(string $column, array $lineData, int $fieldsCount, int &$indent, array $protocolLine): array
    {
        if ($column === 'place') {
            $place = $lineData[$fieldsCount - $indent];
            if (is_numeric($place)) {
                $indent++;
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
            } elseif (preg_match('#\d\d\.\d\d#', $column1)) {
                $indent++;
                return $protocolLine;
            } elseif (str_contains($column1, 'старт')) {
                $indent += 2;
                return $protocolLine;
            } else {
                return $protocolLine;
            }
            $protocolLine['time'] = Carbon::createFromTimeString($timeColumn);
        } elseif ($column === 'rank') {
            $rank = $lineData[$fieldsCount - $indent];
            if (Rank::validateRank($rank)) {
                $indent++;
                $protocolLine['rank'] = $rank;
            }
        }
        return $protocolLine;
    }
}
