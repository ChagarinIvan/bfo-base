<?php

namespace App\Models\Parser;

use App\Models\Group;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use RuntimeException;

class SimplyParser implements ParserInterface
{
    public function parse(UploadedFile $file): Collection
    {
        $doc = new DOMDocument();
        $content = $file->get();
        $content = mb_convert_encoding($content, 'utf-8', 'windows-1251');
        $content = str_replace("&laquo;", '«', $content);
        $content = str_replace("&raquo;", '»', $content);
        @$doc->loadHTML($content);
        $xpath = new DOMXpath($doc);
        $nodes = $xpath->query('//h2|//p[not(./b)]|//p/b|//pre[not(./p[@class])]');
        $linesList = new Collection();
        $groupHeaderIndex = 0;
        $groupName = '';
        $groupHeaderData = [];
        $groupHeaderCount = 0;
        foreach ($nodes as $node) {
            /** @var DOMElement $node */
            if ($node->nodeName === 'h2' || $node->nodeName === 'b' || ($node->nodeName === 'pre' && $groupHeaderCount === 0)) {
                $groupNameLine = mb_convert_encoding($node->nodeValue, 'iso-8859-1', 'utf-8');
                $groupNameLine = str_replace(" ", ' ', $groupNameLine);
                if (empty($groupNameLine) || str_contains($groupNameLine, 'амилия')) {
                    $groupHeaderLine = preg_replace('#\s+#', ' ', $groupNameLine);
                    $groupHeaderLine = trim($groupHeaderLine);
                    $groupHeaderData = explode(' ', $groupHeaderLine);
                    $groupHeaderCount = count($groupHeaderData);
                    $groupHeaderIndex = $groupHeaderCount - 1;
                    if (str_contains($groupHeaderData[$groupHeaderIndex], 'рим')) {
                        $groupHeaderIndex--;
                    }
                } elseif (str_contains($groupNameLine, ' ')) {
                    $groupNameLine = substr($groupNameLine, 0, strpos($groupNameLine, ' '));
                    $groupNameLine = trim($groupNameLine, ' ,');
                    $groupName = Group::FIXING_MAP[$groupNameLine] ?? $groupNameLine;
                    $groupHeaderCount = 0;
                    $groupHeaderData = [];
                }
            } elseif ($node->nodeName === 'p' || $node->nodeName === 'pre') {
                $line = mb_convert_encoding($node->nodeValue, 'iso-8859-1', 'utf-8');
                $line = str_replace(" ", ' ', $line);
                if (empty($line) || str_contains($line, 'амилия')) {
                    continue;
                }
                $preparedLine = preg_replace('#=#', ' ', $line);
                $preparedLine = preg_replace('#\s+#', ' ', $preparedLine);
                $preparedLine = trim($preparedLine);
                $lineData = explode(' ', $preparedLine);
                $fieldsCount = count($lineData);
                if ($fieldsCount <= 5) {
                    continue;
                }
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

    private function getColumn(string $field): string
    {
        $field = mb_strtolower($field);
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

    public function check(UploadedFile $file): bool
    {
        $content = $file->get();
        return str_contains($content, '<o:p></o:p>');
    }

    private function getValue(string $column, array $lineData, int $fieldsCount, int &$indent): mixed
    {
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
            if (preg_match('#^[КМСCKMIбр\/юЮБРкмсkmc]{1,4}$#s', $rank) || in_array($rank, ['КМС', 'б/р'], true)) {
                $protocolLine['rank'] = $rank;
                $indent++;
            }
            return $rank;
        }
        if ($column === 'year') {
            $year = $lineData[$fieldsCount - $indent++];
            return is_numeric($year) ? (int)$year : null;
        }
        return null;
    }
}
