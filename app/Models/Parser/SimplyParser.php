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
        $nodes = $xpath->query('//h2|//p|//pre[not(./p[@class])]');
        $linesList = new Collection();
        foreach ($nodes as $node) {
            /** @var DOMElement $node */
            if ($node->nodeName === 'h2') {
                $groupName = mb_convert_encoding($node->nodeValue, 'iso-8859-1', 'utf-8');
                $groupName = str_replace(" ", ' ', $groupName);
                if (str_contains($groupName, ' ')) {
                    $groupName = substr($groupName, 0, strpos($groupName, ' '));
                }
                $groupName = trim($groupName);
                $groupName = Group::FIXING_MAP[$groupName] ?? $groupName;
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
                if (count($lineData) <= 5) {
                    continue;
                }
                $fieldsCount = count($lineData);
                $protocolLine = ['group' => $groupName];
                $indent = 1;
                $protocolLine['complete_rank'] = $lineData[$fieldsCount - $indent++];
                $place = $lineData[$fieldsCount - $indent++];
                $protocolLine['place'] = is_numeric($place) ? (int)$place : null;

                $time = null;
                try {
                    $time = Carbon::createFromTimeString($lineData[$fieldsCount - $indent++]);
                } catch (Exception) {
                    $time = null;
                }
                $protocolLine['time'] = $time;
                $protocolLine['runner_number'] = (int)$lineData[$fieldsCount - $indent++];
                 if (!is_numeric($protocolLine['runner_number'])) {
                    throw new RuntimeException('Что то не так с номером участника ' . $preparedLine);
                }
                $rank = $lineData[$fieldsCount - $indent];
                if (preg_match('#^[КМСCKMIбр\/юЮБРкмсkmc]{1,4}$#s', $rank) || in_array($rank, ['КМС', 'б/р'], true)) {
                    $protocolLine['rank'] = $rank;
                    $indent++;
                }
                $year = $lineData[$fieldsCount - $indent++];
                $protocolLine['year'] = is_numeric($year) ? (int)$year : null;
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
        return str_contains($content, '<o:p></o:p>');
    }
}
