<?php

namespace App\Models\Parser;

use App\Exceptions\ParsingException;
use App\Models\Group;
use App\Models\Rank;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use RuntimeException;
use function PHPUnit\Framework\isInstanceOf;

class OBelarusNetParser implements ParserInterface
{
    public function parse(UploadedFile $file): Collection
    {
        try {
            $doc = new DOMDocument();
            $content = mb_convert_encoding($file->get(), 'utf-8', 'windows-1251');
            @$doc->loadHTML($content);
            $xpath = new DOMXpath($doc);
            $preNodes = $xpath->query('//pre');
            $linesList = new Collection();
            foreach ($preNodes as $node) {
                $text = trim($node->nodeValue);
                $text = trim($text,'-');
                $text = mb_convert_encoding($text, 'iso-8859-1', 'utf-8');
                $text = trim($text);
                if (!str_contains($text, 'амилия')) {
                    continue;
                }
                $distanceLength = 0;
                $distancePoints = 0;
                $groupNode = $xpath->query('preceding::h2[1]', $node);
                $groupName = $groupNode[0]->nodeValue;
                $groupName = mb_convert_encoding($groupName, 'iso-8859-1', 'utf-8');
                if (str_contains($groupName, ',')) {
                    $groupName = substr($groupName, 0, strpos($groupName, ','));
                }
                $groupName = Group::FIXING_MAP[$groupName] ?? $groupName;

                $lines = preg_split('/\n|\r\n?/', $text);
                $linesCount = count($lines);
                if ($linesCount < 3) {
                    continue;
                }
                $groupHeader = $lines[2];
                $groupHeader = preg_replace('#\s+#', ' ', $groupHeader);
                $groupHeaderData = explode(' ', $groupHeader);
                $groupHeaderIndex = count($groupHeaderData) - 1;

                if (preg_match('#(\d+)\s+[^\d]+,\s+((\d+,\d+)\s+[^\d]+|(\d+)\s+[^\d])#s', $lines[0], $match)) {
                    $distancePoints = (int)$match[1];
                    if (str_contains($match[2], ',')) {
                        $distanceLength = floatval(str_replace(',', '.', $match[3])) * 1000;
                    } else {
                        $distanceLength = floatval($match[4]);
                    }
                }
                for ($index = 4; $index < $linesCount; $index++) {
                    $line = trim($lines[$index]);

                    if (str_contains($line, 'Минаков')) {
                        sleep(1);
                    }
                    if (empty(trim($line, '-'))) {
                        break;
                    }
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
                        if ($columnName === '') {
                            continue;
                        }
                        $protocolLine[$columnName] = $this->getValue($columnName, $lineData, $fieldsCount, $indent, $protocolLine);
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
            throw new ParsingException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    public function check(UploadedFile $file): bool
    {
        return true;
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

    private function getValue(string $column, array $lineData, int $fieldsCount, int &$indent, array &$data): mixed
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
            if ($place === 'в/к') {
                $data['vk'] = true;
                $indent++;
                return null;
            }
            if (is_numeric($place) || $place === '-') {
                $indent++;
                return  (int)$place;
            }
            return null;
        }
        if ($column === 'time') {
            $time = $lineData[$fieldsCount - $indent++];
            if (preg_match('#\d\d\.\d\d#', $time)) {
                $indent++;
                return null;
            }
            try {
                $time = Carbon::createFromTimeString($time);
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
            $year = $lineData[$fieldsCount - $indent];
            if (is_numeric($year)) {
                $indent++;
                return (int)$year;
            } else {
                return null;
            }
        }
        return null;
    }
}
