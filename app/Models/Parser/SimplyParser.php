<?php

declare(strict_types=1);

namespace App\Models\Parser;

use App\Models\Rank;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function array_slice;
use function count;
use function explode;
use function implode;
use function is_numeric;
use function mb_convert_encoding;
use function mb_strtolower;
use function preg_match;
use function preg_replace;
use function str_contains;
use function str_replace;
use function strpos;
use function substr;
use function trim;

class SimplyParser extends AbstractParser
{
    public function parse(string $file, bool $needConvert = true): Collection
    {
        $doc = new DOMDocument();
        $content = mb_convert_encoding($file, 'utf-8', 'windows-1251');
        $content = str_replace(["&laquo;", "&raquo;", " "], ['«', '»', ' '], $content);
        @$doc->loadHTML($content);
        $xpath = new DOMXpath($doc);
        $nodes = $xpath->query('//h2|//p[not(./b)]|//p/b|//pre[not(./p[@class])]');
        $linesList = new Collection();
        $groupHeaderIndex = 0;
        $groupName = '';
        $groupHeaderData = [];
        foreach ($nodes as $node) {
            /** @var DOMElement $node */
            $line = mb_convert_encoding($node->nodeValue, 'iso-8859-1', 'utf-8');
            $line = str_replace(" ", ' ', $line);
            if (empty($line)) {
                continue;
            }

            $withSpace = str_contains($line, ' ');
            if (str_contains($line, 'амилия')) {
                $groupHeaderLine = preg_replace('#\s+#', ' ', $line);
                $groupHeaderLine = trim($groupHeaderLine);
                $groupHeaderData = explode(' ', $groupHeaderLine);
                $groupHeaderIndex = count($groupHeaderData) - 1;
                if (str_contains($groupHeaderData[$groupHeaderIndex], 'рим')) {
                    $groupHeaderIndex--;
                }
            } elseif (
                (
                    ($groupNameLine = trim($line, ' ,')) && $this->groups->containsStrict($groupNameLine)
                ) ||
                (
                    $withSpace &&
                    ($groupNameLine = trim(substr($line, 0, strpos($line, ' ')), ' ,')) &&
                    $this->groups->containsStrict($groupNameLine)
                )
            ) {
                $groupName = $groupNameLine;
                $groupHeaderData = [];
            } elseif ($groupHeaderIndex > 0) {
                $preparedLine = preg_replace('#=#', ' ', $line);
                $preparedLine = preg_replace('#\s+#', ' ', $preparedLine);
                $preparedLine = trim($preparedLine);
                if (!preg_match('#^\d+\s[^\s\d]+#', $preparedLine)) {
                    continue;
                }
                $lineData = explode(' ', $preparedLine);
                $fieldsCount = count($lineData);
                $protocolLine = ['group' => $groupName];
                $indent = 1;
                for ($i = $groupHeaderIndex; $i > 2; $i--) {
                    $columnName = $this->getColumn($groupHeaderData[$i]);
                    if ($columnName === '') {
                        break;
                    }
                    $protocolLine[$columnName] = $this->getValue($columnName, $lineData, $fieldsCount, $indent);
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
        if ($extension === 'html') {
            return str_contains($file, '<o:p></o:p>');
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
        return '';
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
            }
            return null;
        }
        if ($column === 'time') {
            try {
                $time = Carbon::createFromTimeString($lineData[$fieldsCount - $indent++]);
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
            $year = $lineData[$fieldsCount - $indent++];
            return is_numeric($year) ? (int)$year : null;
        }
        return null;
    }
}
