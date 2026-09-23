<?php

declare(strict_types=1);

namespace App\Models\Parser;

use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function array_filter;
use function array_map;
use function array_slice;
use function array_values;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_numeric;
use function mb_check_encoding;
use function mb_convert_encoding;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function preg_split;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strpos;
use function substr;
use function trim;

class AlbatrosTimingParser extends AbstractParser
{
    public function parse(string $file): Collection
    {
        $doc = new DOMDocument();
        $content = $file;
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
            $content = preg_replace('#(charset\s*=\s*[\'\"]?)windows-1251#i', '${1}UTF-8', $content) ?? $content;
        }
        $content = str_replace('&nbsp;', ' ', $content);
        @$doc->loadHTML($content);
        $xpath = new DOMXPath($doc);
        $linesList = new Collection();
        foreach ($this->groupBlocks($xpath) as $groupBlock) {
            $text = trim($groupBlock['text']);
            $text = trim($text, '-');
            $text = trim($text);
            $text = preg_replace_callback(
                '/-{20,}/',
                static fn (array $match): string => "\n{$match[0]}\n",
                $text,
            ) ?? $text;

            $groupName = $groupBlock['group'];
            if (str_contains($groupName, ',')) {
                $groupName = substr($groupName, 0, strpos($groupName, ','));
            }

            $lines = array_values(array_filter(
                preg_split('/\n|\r\n?/', $text),
                static fn (string $line): bool => trim($line) !== '',
            ));
            $linesCount = count($lines);
            $distance = $lines[0];
            $distanceLength = 0;
            $distancePoints = 0;
            if (preg_match('#(\d+)\s+[^\d]+,\s+((\d+,\d+)\s+[^\d]+|(\d+)\s+[^\d])#s', $distance, $match)) {
                $distancePoints = (int)$match[1];
                $distanceLength = str_contains($match[2], ',') ? (float)str_replace(',', '.', $match[3]) * 1000 : (float)$match[4];
            } elseif (count($lines) < 4) {
                continue;
            }
            $groupHeader = $lines[2];
            $withPoints = str_contains($groupHeader, 'Oчки') || str_contains($groupHeader, 'Очки');
            $withComment = str_contains($groupHeader, 'Прим');
            $withCompletedRank = str_contains($groupHeader, 'Вып') || str_contains($groupHeader, 'вып');
            for ($index = 4; $index < $linesCount; $index++) {
                $line = trim($lines[$index]);
                if (in_array(trim($line, '-'), ['', '0'], true)) {
                    break;
                }
                $preparedLine = preg_replace('#\s+#', ' ', $line);
                $preparedLine = preg_replace(
                    '/п\.?\s*п\.?\s*20[.,]10(?:\s+-){2,}/u',
                    'пп - -',
                    $preparedLine,
                ) ?? $preparedLine;
                $lineData = explode(' ', $preparedLine);
                $fieldsCount = count($lineData);
                if ($fieldsCount < 6 || !is_numeric($lineData[0])) {
                    continue;
                }
                $protocolLine = [
                    'group' => $groupName,
                    'distance' => [
                        'length' => $distanceLength,
                        'points' => $distancePoints,
                    ],
                ];
                $indent = 1;
                if ($withComment && str_contains($lineData[$fieldsCount - $indent], 'ично')) {
                    $indent++;
                }
                if ($withPoints) {
                    $points = $lineData[$fieldsCount - $indent++];
                    if ($points === 'в/к') {
                        $protocolLine['vk'] = true;
                    }
                    if (preg_match('#\d?\d:\d\d:\d\d#', $points)) {
                        $points = $lineData[$fieldsCount - $indent++];
                        if ($points === 'в/к') {
                            $protocolLine['vk'] = true;
                        }
                    }
                    $protocolLine['points'] = is_numeric($points) ? (int)$points : null;
                }
                if ($withCompletedRank) {
                    $completeRank = $lineData[$fieldsCount - $indent];
                    if (($this->rankNormalizer->isValid($completeRank) || $completeRank === '-') && !in_array($completeRank, ['1', '2', '3'], true)) {
                        $protocolLine['complete_rank'] = $completeRank;
                        $indent++;
                    } elseif ($completeRank === 'в/к') {
                        $protocolLine['vk'] = true;
                    }
                }

                $protocolLine['place'] = null;
                $place = $lineData[$fieldsCount - $indent++];
                if (is_numeric($place)) {
                    $protocolLine['place'] = (int)$place;
                } elseif ($place === 'в/к') {
                    $protocolLine['vk'] = true;
                    $protocolLine['place'] = null;
                }

                $time = null;
                try {
                    $number = $lineData[$fieldsCount - ($indent + 1)];
                    if ($number === 'пп' || str_starts_with($number, 'п.п.')) {
                        $indent++;
                        $indent++;
                        throw new Exception();
                    }
                    $time = Carbon::createFromTimeString($lineData[$fieldsCount - ($indent++)]);
                } catch (Exception) {
                    $time = null;
                }
                $protocolLine['time'] = $time;
                $protocolLine['runner_number'] = (int)$lineData[$fieldsCount - $indent++];
                $protocolLine['rank'] = $lineData[$fieldsCount - $indent++];

                if (is_numeric($lineData[$fieldsCount - $indent])) {
                    $protocolLine['year'] = (int)$lineData[$fieldsCount - $indent];
                } else {
                    $protocolLine['year'] = null;
                    $indent--;
                }
                $protocolLine['serial_number'] = (int)$lineData[0];
                $protocolLine['lastname'] = $lineData[1];
                $protocolLine['firstname'] = $lineData[2];
                $protocolLine['club'] = implode(' ', array_slice($lineData, 3, $fieldsCount - $indent - 3));
                $linesList->push($protocolLine);
            }
        }

        return $linesList;
    }

    public function check(string $file, string $extension): bool
    {
        if (!str_contains($extension, 'htm')) {
            return false;
        }

        $doc = new DOMDocument();
        if (str_contains($file, 'Albatros-Timing')) {
            return true;
        }

        @$doc->loadHTML($file);
        $xpath = new DOMXPath($doc);
        $preNodes = $xpath->query('//pre');

        if ($preNodes->length > 0) {
            $firstItem = $preNodes->item(0);
            return str_contains($firstItem->nodeValue, 'Параметры дистанции');
        }

        return  false;
    }

    /** @return list<array{group: string, text: string}> */
    private function groupBlocks(DOMXPath $xpath): array
    {
        $blocks = [];
        $blockIndex = null;

        foreach ($xpath->query('//h2 | //pre') as $node) {
            if ($node->nodeName === 'h2') {
                $blocks[] = [
                    'group' => trim(str_replace("\u{00A0}", ' ', $node->nodeValue)),
                    'parts' => [],
                ];
                $blockIndex = count($blocks) - 1;

                continue;
            }

            if ($blockIndex !== null) {
                $blocks[$blockIndex]['parts'][] = str_replace("\u{00A0}", ' ', $node->nodeValue);
            }
        }

        return array_map(static fn (array $block): array => [
            'group' => $block['group'],
            'text' => implode("\n", $block['parts']),
        ], $blocks);
    }
}
