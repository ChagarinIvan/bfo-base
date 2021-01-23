<?php

namespace App\Models\Parser;

use App\Models\Group;
use App\Models\Rank;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use RuntimeException;

class WinOrientHtmlParser implements ParserInterface
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
            if (!str_contains($text, 'амилия')) {
                continue;
            }
            $groupNode = $xpath->query('preceding::h2[1]', $node);
            $groupName = $groupNode[0]->nodeValue;
            $groupName = explode(',', $groupName)[0];
            $groupName = Group::FIXING_MAP[$groupName] ?? $groupName;

            $lines = preg_split('/\n|\r\n?/', $text);
            $linesCount = count($lines);
            if ($linesCount < 2) {
                continue;
            }
            for ($index = 1; $index < $linesCount; $index++) {
                $line = trim($lines[$index]);
                if (empty(trim($line, '-'))) {
                    break;
                }
                $preparedLine = preg_replace('#\s+#', ' ', $line);
                $lineData = explode(' ', $preparedLine);
                $fieldsCount = count($lineData);
                $protocolLine = ['group' => $groupName];
                $indent = 1;
                $place = $lineData[$fieldsCount - $indent++];
                $protocolLine['place'] = is_numeric($place) ? (int)$place : null;

                try {
                    $time = $lineData[$fieldsCount - $indent];
                    if (!str_contains($time, '+')) {
                        throw new RuntimeException('wrong time');
                    } else {
                        $indent++;
                    }
                    $time = $lineData[$fieldsCount - $indent];
                    $time = Carbon::createFromTimeString($time);
                    $indent++;
                } catch (\Exception $e) {
                    $time = null;
                }
                $protocolLine['time'] = $time;
                $year = $lineData[$fieldsCount - $indent];
                if (is_numeric($year) && preg_match('#\d{4}#', $year)) {
                    $protocolLine['year'] = (int)$year;
                    $indent++;
                } else {
                    $protocolLine['year'] = null;
                }
                $protocolLine['runner_number'] = (int)$lineData[$fieldsCount - $indent++];
                if (!is_numeric($protocolLine['runner_number'])) {
                    throw new RuntimeException('Что то не так с номером участника '.$preparedLine);
                }

                $rank = $lineData[$fieldsCount - $indent];
                if (preg_match('#^[КМСKMIбр\/юЮБРкмсkmc]{1,4}$#', $rank) || in_array($rank, Rank::RANKS, true)) {
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
        return str_contains($file->get(), '<title>WinOrient');
    }
}
