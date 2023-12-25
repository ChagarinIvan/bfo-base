<?php
declare(strict_types=1);

namespace App\Models\Parser;

use App\Models\Rank;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;
use function array_slice;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_numeric;
use function preg_match;
use function preg_replace;
use function preg_split;
use function str_contains;
use function str_replace;
use function strpos;
use function substr;
use function trim;

class AlbatrosTimingParser extends AbstractParser
{
    public function parse(string $file, bool $needConvert = true): Collection
    {
        $doc = new DOMDocument();
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
                    $distanceLength = (float)str_replace(',', '.', $match[3]) * 1000;
                } else {
                    $distanceLength = (float)$match[4];
                }
            } elseif (count($lines) < 4) {
                continue;
            }
            $groupHeader = $lines[2];
            $withPoints = str_contains($groupHeader, 'Oчки') || str_contains($groupHeader, 'Очки');
            $withComment = str_contains($groupHeader, 'Прим');
            for ($index = 4; $index < $linesCount; $index++) {
                $line = trim($lines[$index]);
                if (empty(trim($line, '-'))) {
                    break;
                }
                $preparedLine = preg_replace('#\s+#', ' ', $line);
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
                if ($withComment && str_contains($lineData[$fieldsCount - $indent], 'ично')) {
                    $indent++;
                }
                if ($withPoints) {
                    $points = $lineData[$fieldsCount - $indent++];
                    if ($points === 'в/к') {
                        $protocolLine['vk'] = true;
                    }
                    $protocolLine['points'] = is_numeric($points) ? (int)$points : null;
                }
                $completeRank = $lineData[$fieldsCount - $indent];
                if ((Rank::validateRank($completeRank) || $completeRank === '-') && !in_array($completeRank, ['1', '2', '3'], true)) {
                    $protocolLine['complete_rank'] = $completeRank;
                    $indent++;
                } elseif ($completeRank === 'в/к') {
                    $protocolLine['vk'] = true;
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
                    if ($number === 'пп') {
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
                if (!is_numeric($protocolLine['runner_number'])) {
                    throw new RuntimeException('Что то не так с номером участника ' . $preparedLine);
                }
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
        $xpath = new DOMXpath($doc);
        $preNodes = $xpath->query('//pre');

        if ($preNodes->length > 0) {
            $firstItem = $preNodes->item(0);
            return str_contains($firstItem->nodeValue, 'Параметры дистанции');
        }

        return  false;
    }
}
