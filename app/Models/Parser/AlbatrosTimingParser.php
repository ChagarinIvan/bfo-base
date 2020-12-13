<?php

namespace App\Models\Parser;

use App\Models\Group;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use RuntimeException;

class AlbatrosTimingParser implements ParserInterface
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
            $groupNode = $xpath->query('preceding::h2[1]', $node);
            $groupName = $groupNode[0]->nodeValue;
            if (str_contains($groupName, ',')) {
                $groupName = substr($groupName, 0, strpos($groupName, ','));
            }
            $groupName = Group::FIXING_MAP[$groupName] ?? $groupName;

            $lines = preg_split('/\n|\r\n?/', $text);
            $linesCount = count($lines);
            if ($linesCount < 5) {
                continue;
            }
            $groupHeader = $lines[2];
            $withPoints = str_contains($groupHeader, 'Oчки');
            for ($index = 4; $index < $linesCount; $index++) {
                $line = trim($lines[$index]);
                if (empty(trim($line, '-'))) {
                    break;
                }
                $preparedLine = preg_replace('#\s+#', ' ', $line);
                $lineData = explode(' ', $preparedLine);
                $fieldsCount = count($lineData);
                $protocolLine = ['group' => $groupName];
                $indent = 1;
                if ($withPoints) {
                    $points = $lineData[$fieldsCount - $indent++];
                    $protocolLine['points'] = is_numeric($points) ? (int)$points : null;
                }
                $protocolLine['complete_rank'] = $lineData[$fieldsCount - $indent++];
                $place = $lineData[$fieldsCount - $indent++];
                $protocolLine['place'] = is_numeric($place) ? (int)$place : null;
                $time = null;
                try {
                    $time = Carbon::createFromTimeString($lineData[$fieldsCount - $indent++]);
                } catch (\Exception $e) {
                    $time = null;
                }
                $protocolLine['time'] = $time;
                $protocolLine['runner_number'] = (int)$lineData[$fieldsCount - $indent++];
                if (!is_numeric($protocolLine['runner_number'])) {
                    throw new RuntimeException('Что то не так с номером участника '.$preparedLine);
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

    public function check(UploadedFile $file, string $type = null): bool
    {
        $doc = new DOMDocument();
        $content = $file->get();
        if (str_contains($content, 'Albatros-Timing')) {
            return true;
        }

        @$doc->loadHTML($content);
        $xpath = new DOMXpath($doc);
        $preNodes = $xpath->query('//pre');

        if ($preNodes->length > 0) {
            $firstItem = $preNodes->item(0);
            return str_contains($firstItem->nodeValue, 'Параметры дистанции');
        }
        return  false;
    }
}
