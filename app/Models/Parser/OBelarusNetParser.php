<?php

namespace App\Models\Parser;

use App\Models\Group;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use RuntimeException;

class OBelarusNetParser implements ParserInterface
{
    public function parse(UploadedFile $file): Collection
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($file->get());
        $xpath = new DOMXpath($doc);
        $preNodes = $xpath->query('//pre');
        $linesList = new Collection();
        foreach ($preNodes as $node) {
            $text = trim($node->nodeValue);
            $text = trim($text,'-');
            $text = trim($text);
            if (!str_contains($text, 'амилия')) {
                continue;
            }
            $groupNode = $xpath->query('preceding::h2[1]', $node);
            $groupName = $groupNode[0]->nodeValue;
            if (str_contains($groupName, ',')) {
                $groupName = substr($groupName, 0, strpos($groupName, ','));
            }
            $groupName = Group::FIXING_MAP[$groupName] ?? $groupName;

            $lines = preg_split('/\n|\r\n?/', $text);
            $linesCount = count($lines);
            if ($linesCount < 3) {
                continue;
            }
            $groupHeader = $lines[0];
            $withPoints = str_contains($groupHeader, 'Oчки');
            for ($index = 2; $index < $linesCount; $index++) {
                $line = trim($lines[$index]);
                if (empty(trim($line, '-'))) {
                    break;
                }
                $preparedLine = preg_replace('#=#', ' ', $line);
                $preparedLine = preg_replace('#\s+#', ' ', $preparedLine);
                $lineData = explode(' ', $preparedLine);
                $fieldsCount = count($lineData);
                $protocolLine = ['group' => $groupName];
                $indent = 1;
                if ($withPoints) {
                    $points = $lineData[$fieldsCount - $indent++];
                    if (is_numeric($points)) {
                        $protocolLine['points'] = (int)$points;
                    } elseif ($points === '-') {
                        $protocolLine['points'] = null;
                    } else {
                        $points = $lineData[$fieldsCount - $indent++];
                        $protocolLine['points'] = is_numeric($points) ? (int)$points : null;
                    }
                }
                $completeRank = $lineData[$fieldsCount - $indent++];
                $isPP = str_starts_with($lineData[$fieldsCount - $indent], 'пп');
                if (is_numeric($completeRank)) {
                    $protocolLine['complete_rank'] = '-';
                    $place = $completeRank;
                } else {
                    $protocolLine['complete_rank'] = $isPP ? '-' : $completeRank;
                    $place = $lineData[$fieldsCount - $indent++];
                }
                $protocolLine['place'] = is_numeric($place) ? (int)$place : null;
                if ($isPP) {
                    $time = null;
                } else {
                    $time = null;
                    try {
                        $time = Carbon::createFromTimeString($lineData[$fieldsCount - $indent++]);
                    } catch (\Exception $e) {
                        $time = null;
                    }
                }
                $protocolLine['time'] = $time;
                $year = $lineData[$fieldsCount - $indent++];
                $protocolLine['year'] = is_numeric($year) ? (int)$year : null;
                $protocolLine['runner_number'] = (int)$lineData[$fieldsCount - $indent++];
                if (!is_numeric($protocolLine['runner_number'])) {
                    throw new RuntimeException('Что то не так с номером участника '.$preparedLine);
                }
                $rank = $lineData[$fieldsCount - $indent];
                if (preg_match('#^[КМСCKMIбр\/юЮБРкмсkmc]{1,4}$#s', $rank) || in_array($rank, ['КМС', 'б/р'], true)) {
                    $protocolLine['rank'] = $rank;
                    $indent++;
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

    public function check(UploadedFile $file): bool
    {
        $content = $file->get();
        $result = preg_match('#pre>\n-#', $content, $m);
        return true;
    }
}
