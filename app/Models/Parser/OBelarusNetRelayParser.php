<?php

namespace App\Models\Parser;

use App\Models\Group;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use RuntimeException;
use function PHPUnit\Framework\stringContains;

class OBelarusNetRelayParser implements ParserInterface
{
    private $commandPlace = 0;
    private $commandPoints = 0;
    private $commandRank = '';
    private $commandCounter = 0;


    public function parse(UploadedFile $file): Collection
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($file->get());
        $xpath = new DOMXpath($doc);
        $preNodes = $xpath->query('//pre');
        $linesList = new Collection();
        foreach ($preNodes as $node) {
            $this->commandCounter = 0;
            $this->commandPoints = 0;
            $this->commandPlace = 0;
            $this->commandRank = '';

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
            if ($linesCount < 3) {
                continue;
            }
            $groupHeader = $lines[0];
            $groupHeader = trim($groupHeader);
            $groupHeader = preg_replace('#\s+#', ' ', $groupHeader);
            $headers = explode(' ', $groupHeader);
            $count = count($headers);

            $withPoints = str_contains($groupHeader, 'Очки');
            $isOpen = str_contains($groupName, 'OPEN');
            for ($index = ($isOpen ? 1 : 3); $index < $linesCount; $index++) {
                $line = trim($lines[$index]);
                if (is_numeric($line)) {
                    $this->commandCounter = 0;
                    $this->commandPoints = 0;
                    $this->commandPlace = 0;
                    $this->commandRank = '';
                    continue;
                }
                if (empty($line)) {
                    break;
                }
                $preparedLine = preg_replace('#=#', ' ', $line);
                $preparedLine = preg_replace('#\s+#', ' ', $preparedLine);
                $lineData = explode(' ', $preparedLine);
                $fieldsCount = count($lineData);
                $protocolLine = ['group' => $groupName];
                $indent = 1;
                $protocolLine['lastname'] = $lineData[1];
                $protocolLine['firstname'] = $lineData[2];
                if ($isOpen) {
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
                    $protocolLine['club'] = implode(' ', array_slice($lineData, 3, $fieldsCount - $indent - 2));
                } else {
                    for ($i = $count - 1; $i > 3; $i--) {
                        $header = $headers[$i];
                        $value = $lineData[$fieldsCount - $indent];
                        try {
                            if (is_numeric($value) && $value !== '24.4' && !str_contains($value, '20.10')) {
                                throw new \Exception();
                            }
                            if ($value === '24.4') {
                                $indent += 2;
                                continue;
                            }
                            if ($value !== 'DSQ' && !str_contains($value, '20.10') && $header !== 'Время') {
                                Carbon::createFromTimeString($value);
                                $value = $lineData[$fieldsCount - $indent - 1];

                                try {
                                    Carbon::createFromTimeString($value);
                                    $indent++;
                                } catch (\Exception $e) {}
                            }

                            if ($header === 'Время') {
                                foreach ($lineData as $key => $data) {
                                    if (strlen($data) < 4) {
                                        continue;
                                    }
                                    try {
                                        Carbon::createFromTimeString($data);
                                        $indent = $fieldsCount - $key;
                                        break;
                                    } catch (\Exception $e) {}
                                }
                            }

                            for ($n = $count - 1; $n !== 0; $n--) {
                                $headerN = $headers[$n];
                                if (str_contains($headerN, 'ремя')) {
                                    break;
                                }
                            }
                            $i = $n;
                            break;
                        } catch (\Exception $e) {
                            $isNext = $this->parseByHeader($header, $value, $protocolLine);
                            if ($isNext) {
                                $indent++;
                            }
                        }
                    }

                    $protocolLine['runner_number'] = (int)$lineData[0];
                    $protocolLine['serial_number'] = $protocolLine['runner_number'];
                    try {
                        $i--;
                        $time = trim($lineData[$fieldsCount - $indent]);
                        if (str_contains($value, '20.10') || $time === 'DSQ') {
                            $protocolLine['time'] = null;
                        } else {
                            $time = Carbon::createFromTimeString($time);
                            $protocolLine['time'] = $time;
                        }
                        if (str_contains($value, '20.10')) {
                            $indent++;
                        }
                        $indent++;
                    } catch (\Exception $e) {
                        $protocolLine['time'] = null;
                        if (isset($protocolLine['complete_rank'])) {
                            $protocolLine['rank'] = $protocolLine['complete_rank'];
                            unset($protocolLine['complete_rank']);
                        }
                    }
                    for ($i--; $i > 3; $i--) {
                        $header = $headers[$i];
                        $value = $lineData[$fieldsCount - $indent];
                        $isNext = $this->parseByHeader($header, $value, $protocolLine);
                        if ($isNext) {
                            $indent++;
                        }
                    }
                    $protocolLine['club'] = implode(' ', array_slice($lineData, 3, $fieldsCount - $indent - 2));
                    if ($this->commandCounter > 0) {
                        $protocolLine['place'] = $this->commandPlace === 0 ? null : $this->commandPlace;
                        if (!empty($this->commandRank)) {
                            $protocolLine['complete_rank'] = $this->commandRank;
                        }
                        $protocolLine['points'] = $this->commandPoints === 0 ? null : $this->commandPoints;
                    }
                }

                $linesList->push($protocolLine);
                $this->commandCounter++;
            }
        }
        return $linesList;
    }

    public function check(UploadedFile $file, string $type = null): bool
    {
        if (str_contains($type, 'relay')) {
            $content = $file->get();
            return preg_match('#<b>\d#', $content);
        }
        return false;
    }

    private function parseByHeader(string $header, string $value, array &$protocolLine): bool
    {
        if ($header === 'Место') {
            if ($this->commandCounter > 0) {
                return false;
            }
            if (is_numeric($value)) {
                $protocolLine['place'] = (int)$value;
                $this->commandPlace = $protocolLine['place'];
            } else {
                $protocolLine['place'] = null;
            }
            return true;
        }
        if ($header === 'Очки') {
            if ($this->commandCounter > 0) {
                return false;
            }
            if (is_numeric($value)) {
                $protocolLine['points'] = (int)$value;
                $this->commandPoints = $protocolLine['points'];
            } elseif ($value === '-') {
                $protocolLine['points'] = null;
            }
            return true;
        }
        if ($header === 'Прим') {
            if ($value === 'в/к' || $value === 'лично') {
                return true;
            }
        }
        if ($header === 'Вып') {
            if ($this->commandCounter > 0) {
                return false;
            }
            if (preg_match('#^[КМСCKMIбр\/юЮБРкмсkmc]{1,4}$#s', $value) || in_array($value, ['КМС', 'б/р'], true)) {
                $protocolLine['complete_rank'] = $value;
                $this->commandRank = $value;
            }
            return true;
        }
        if ($header === 'Квал') {
            if (preg_match('#^[КМСCKMIбр\/юЮБРкмсkmc]{1,4}$#s', $value) || in_array($value, ['КМС', 'б/р', 'IIIю'], true)) {
                $protocolLine['rank'] = $value;
                return true;
            }
        }
        if ($header === 'ГР' || $header === 'г.р.') {
            if (is_numeric($value)) {
                $protocolLine['year'] = (int)$value;
                return true;
            } else {
                $protocolLine['year'] = null;
                return false;
            }
        }
        return false;
    }
}
