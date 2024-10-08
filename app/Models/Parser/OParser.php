<?php

declare(strict_types=1);

namespace App\Models\Parser;

use App\Domain\Rank\Rank;
use Exception;
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
use function strip_tags;
use function trim;

/**
 * Class OParser
 *
 * Брестский подснежник 2021
 *
 * @package App\Models\Parser
 */
class OParser extends AbstractParser
{
    private bool $setVk = false;

    public function parse(string $file, bool $needConvert = true): Collection
    {
        $lines = preg_split('/\n|\r\n?/', $file);
        $linesList = new Collection();
        $startGroupHeader = false;
        $startProtocol = false;
        $groupHeaderData = [];
        $groupHeaderIndex = 0;
        $groupName = '';
        $distanceLength = 0;
        $distancePoints = 0;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('#<h2>([^\d]\d\d[^\d]?|[]^\d]{5,6})</h2>#uism', $line, $match)) {
                $groupName = $match[1];
                $distanceLength = 0;
                $distancePoints = 0;
                if (preg_match('#:\s+(\d+)\s+[^\d]+,\s+(\d+,\d+)\s+[^\d]+#s', $line, $match)) {
                    $distancePoints = (int)$match[1];
                    $distanceLength = (float) (str_replace(',', '.', $match[2])) * 1000;
                }
                continue;
            }

            if (preg_match('#:\s+(\d+)\s+[^\d]+,\s+(\d+,\d+)\s+[^\d]+#s', $line, $match)) {
                $distancePoints = (int)$match[1];
                $distanceLength = (float) (str_replace(',', '.', $match[2])) * 1000;
                continue;
            }

            $line = strip_tags($line);
            if ($line === '') {
                continue;
            }
            if (trim($line, '-') === '') {
                if ($startGroupHeader) {
                    $startGroupHeader = false;
                    $startProtocol = true;
                } elseif ($startProtocol) {
                    $startGroupHeader = false;
                    $startProtocol = false;
                } else {
                    $startGroupHeader = true;
                    $startProtocol = false;
                }
                continue;
            }

            if ($startGroupHeader) {
                $groupHeaderLine = preg_replace('#\s+#', ' ', $line);
                $groupHeaderLine = trim($groupHeaderLine);
                $groupHeaderData = explode(' ', $groupHeaderLine);
                $groupHeaderIndex = count($groupHeaderData) - 1;
                if (str_contains($groupHeaderData[$groupHeaderIndex], 'рим')) {
                    $groupHeaderIndex--;
                }
            }

            if ($startProtocol) {
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

                for ($i = $groupHeaderIndex; $i > 2; $i--) {
                    $columnName = $this->getColumn($groupHeaderData[$i]);
                    if ($columnName === null) {
                        break;
                    }

                    if ($columnName === '' && $lineData[$fieldsCount - $indent] === 'снят') {
                        continue;
                    }

                    if ($columnName === '') {
                        $indent++;
                        continue;
                    }
                    $protocolLine[$columnName] = $this->getValue($columnName, $lineData, $fieldsCount, $indent);
                }

                if ($this->setVk) {
                    $protocolLine['vk'] = true;
                    $this->setVk = false;
                } else {
                    $protocolLine['vk'] = false;
                }
                $protocolLine['serial_number'] = (int)$lineData[0];
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
        if (str_contains($extension, 'html')) {
            return (bool)preg_match('#<h2>\w{3}</h2>Параметр#u', $file);
        }

        return false;
    }

    private function getValue(string $column, array $lineData, int $fieldsCount, int &$indent): mixed
    {
        if ($column === 'points') {
            $points = $lineData[$fieldsCount - $indent++];
            return is_numeric($points) ? (int)$points : null;
        }
        if ($column === 'complete_rank') {
            return $lineData[$fieldsCount - $indent++];
        }
        if ($column === 'place') {
            $place = $lineData[$fieldsCount - $indent];
            if (is_numeric($place) || $place === '-') {
                $indent++;
                return  (int)$place;
            } elseif ($place === 'в/к') {
                $indent++;
                $this->setVk = true;
            }
            return null;
        }
        if ($column === 'time') {
            $time = $lineData[$fieldsCount - $indent++];
            if (preg_match('#:\d\d:\d\d#', $time)) {
                try {
                    $time = Carbon::createFromTimeString($time);
                } catch (Exception) {
                    $time = null;
                }
            } elseif (preg_match('#\d\d.\d\d#', $time)) {
                $indent++;
                $time = null;
            } else {
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
            } else {
                return '';
            }
        }
        if ($column === 'year') {
            $year = $lineData[$fieldsCount - $indent];
            if (is_numeric($year) && preg_match('#\d{4}#', $year)) {
                $indent++;
                return (int)$year;
            } else {
                return null;
            }
        }
        return null;
    }

    private function getColumn(string $field): ?string
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
        if (str_contains($field, 'зультат')) {
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
        if (str_contains($field, 'ставан')) {
            return '';
        }
        return null;
    }
}
