<?php

namespace App\Models\Parser;

use App\Models\Group;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class OBelarusNetRelayWithHeadersParser implements ParserInterface
{
    public function parse(UploadedFile $file): Collection
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($file->get());
        $xpath = new DOMXpath($doc);
        $preNodes = $xpath->query('//pre');
        $linesList = new Collection();
        foreach ($preNodes as $node) {
            $commandCounter = 0;
            $commandPoints = 0;
            $commandPlace = 0;
            $commandRank = '';

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
            $groupName = trim($groupName, '+');
            $groupName = Group::FIXING_MAP[$groupName] ?? $groupName;

            $lines = preg_split('/\n|\r\n?/', $text);
            $linesCount = count($lines);
            if ($linesCount < 2) {
                continue;
            }

            for ($index = 2; $index < $linesCount; $index++) {
                $line = trim($lines[$index]);
                if (empty($line)) {
                    break;
                }
                $preparedLine = preg_replace('#=#', ' ', $line);
                $preparedLine = preg_replace('#\s+#', ' ', $preparedLine);
                $lineData = explode(' ', $preparedLine);
                $fieldsCount = count($lineData);
                if (($fieldsCount === 4 || $fieldsCount === 3) && is_numeric($lineData[0])) {
                    $commandCounter = 0;
                    $commandPoints = is_numeric($lineData[3]) ? (int)$lineData[3] : null;
                    $commandPlace = is_numeric($lineData[1]) ? (int)$lineData[1] : null;
                    $commandRank = $lineData[2] !== '-' ? $lineData[2] : null;
                    continue;
                }
                $protocolLine = ['group' => $groupName];
                $indent = 1;
                $protocolLine['lastname'] = $lineData[1];
                $protocolLine['firstname'] = $lineData[2];
                $value = $lineData[$fieldsCount - $indent];
                try {
                    $time = Carbon::createFromTimeString($value);
                    $value = $lineData[$fieldsCount - $indent - 1];

                    try {
                        $protocolLine['time'] = Carbon::createFromTimeString($value);
                        $indent++;
                    } catch (\Exception $e) {
                        $protocolLine['time'] = $time;
                    }
                } catch (\Exception $e) {
                    $protocolLine['time'] = null;
                }
                $protocolLine['runner_number'] = (int)$lineData[0];
                $protocolLine['serial_number'] = $protocolLine['runner_number'];
                $value = $lineData[$fieldsCount - $indent++ - 1];

                if (preg_match('#^[КМСCKMIбр\/юЮБРкмсkmc]{1,4}$#s', $value) || in_array($value, ['КМС', 'б/р'], true)) {
                    $protocolLine['rank'] = $value;
                    $indent++;
                }

                $protocolLine['club'] = implode(' ', array_slice($lineData, 3, $fieldsCount - $indent - 2));
                $protocolLine['place'] = $commandPlace;
                if ($commandRank !== null) {
                    $protocolLine['complete_rank'] = $commandRank;
                }
                $protocolLine['points'] = $commandPoints;

                $linesList->push($protocolLine);
                $commandCounter++;
            }
        }

        return $linesList;
    }

    public function check(UploadedFile $file, string $type = null): bool
    {
        if (str_contains($type, 'relay')) {
            $content = $file->get();
            return preg_match('#\d+\s+(-|\d+|в/к)\s+(-|.{1,4})\s+(-|\d{1,3})?#', $content);
        }
        return false;
    }
}
