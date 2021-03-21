<?php

namespace App\Models\Parser;

use App\Exceptions\ParsingException;
use App\Models\Group;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class WinOrientHtmlParser implements ParserInterface
{
    /**
     * @param UploadedFile $file
     * @return Collection
     * @throws ParsingException
     */
    public function parse(UploadedFile $file): Collection
    {
        try {
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
                $groupHeaderData = [];
                $groupHeaderIndex = 0;
                $isFirst = true;
                for ($index = 1; $index < $linesCount; $index++) {
                    $line = trim($lines[$index]);
                    if (str_contains($line, 'Комаров')) {
                        sleep(1);
                    }
                    if (empty(trim($line, '-'))) {
                        if ($isFirst) {
                            $isFirst = false;
                            continue;
                        } else {
                            break;
                        }
                    }
                    if (str_contains($line, 'амилия')) {
                        $groupHeaderLine = preg_replace('#\s+#', ' ', $line);
                        $groupHeaderLine = trim($groupHeaderLine);
                        $groupHeaderData = explode(' ', $groupHeaderLine);
                        $groupHeaderIndex = count($groupHeaderData) - 1;
                        if (str_contains($groupHeaderData[$groupHeaderIndex], 'рим')) {
                            $groupHeaderIndex--;
                        }
                        continue;
                    }
                    $preparedLine = str_replace('=', '', $line);
                    $preparedLine = preg_replace('#\s+#', ' ', $preparedLine);
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
        } catch (Exception $e) {
            throw new ParsingException($e->getMessage(), $e->getCode(), $e->getPrevious(),);
        }
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
        if ($field ==='гр' || $field === 'г.р.') {
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
            $time = $lineData[$fieldsCount - $indent++];
            if (preg_match('#:\d\d:\d\d#', $time)) {
                try {
                    $time = Carbon::createFromTimeString($time);
                } catch (Exception) {
                    $time = null;
                }
            } else {
                $indent++;
                $time = null;
            }

            return $time;
        }
        if ($column === 'runner_number') {
            return (int)$lineData[$fieldsCount - $indent++];
        }
        if ($column === 'rank') {
            $rank = $lineData[$fieldsCount - $indent];
            if (preg_match('#^[КМСCKMIбр\/юЮБРкмсkmc]{1,4}$#s', $rank) || in_array($rank, ['КМС', 'б/р'], true)) {
                $indent++;
                return $rank;
            } else {
                return '';
            }
        }
        if ($column === 'year') {
            $year = $lineData[$fieldsCount - $indent++];
            return is_numeric($year) ? (int)$year : null;
        }
        return null;
    }

    public function check(UploadedFile $file): bool
    {
        return str_contains($file->get(), '<title>WinOrient');
    }
}
