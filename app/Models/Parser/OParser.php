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
use RuntimeException;

class OParser implements ParserInterface
{
    private bool $setVk = false;

    public function parse(UploadedFile $file): Collection
    {
        try {
            $content = $file->get();
            $content = strip_tags($content);
            $lines = preg_split('/\n|\r\n?/', $content);
            $linesList = new Collection();
            $startGroups = false;
            $startGroupHeader = false;
            $startProtocol = false;
            $groupHeaderData = [];
            $groupHeaderIndex = 0;
            $groupName = '';

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                if (!$startGroups && preg_match('#^[^\d]\d\d#uism', $line)) {
                    $startGroups = true;
                    if (str_contains($line, ',')) {
                        $line = substr($line, 0, strpos($line, ','));
                    }
                    $groupName = Group::FIXING_MAP[$line] ?? $line;
                    continue;
                }

                if ($startGroups) {
                    if (trim($line, '-') === '') {
                        if ($startGroupHeader) {
                            $startGroupHeader = false;
                            $startProtocol = true;
                        } elseif ($startProtocol) {
                            $startGroupHeader = false;
                            $startProtocol = false;
                        } else {
                            $startGroupHeader = true;
                            $startProtocol = false;
                        }
                        continue;
                    }

                    if ($startGroupHeader) {
                        $groupHeaderLine = preg_replace('#\s+#', ' ', $line);
                        $groupHeaderLine = trim($groupHeaderLine);
                        $groupHeaderData = explode(' ', $groupHeaderLine);
                        $groupHeaderIndex = count($groupHeaderData) - 1;
                        if (str_contains($groupHeaderData[$groupHeaderIndex], 'рим')) {
                            $groupHeaderIndex--;
                        }
                    }

                    if ($startProtocol) {
                        //if (str_contains($line, 'Злата')) {
                        //    sleep(1);
                        //}
                        $preparedLine = preg_replace('#=#', ' ', $line);
                        $preparedLine = preg_replace('#\s+#', ' ', $preparedLine);
                        $lineData = explode(' ', $preparedLine);
                        $fieldsCount = count($lineData);
                        $protocolLine = ['group' => $groupName];
                        $indent = 1;

                        for ($i = $groupHeaderIndex; $i > 2; $i--) {
                            $columnName = $this->getColumn($groupHeaderData[$i]);
                            if ($columnName === null) {
                                break;
                            } elseif ($columnName === '' && $lineData[$fieldsCount - $indent] === 'снят') {
                                continue;
                            } elseif ($columnName === '') {
                                $indent++;
                                continue;
                            }
                            $protocolLine[$columnName] = $this->getValue($columnName, $lineData, $fieldsCount, $indent);
                        }

                        if ($this->setVk) {
                            $protocolLine['vk'] = true;
                            $this->setVk = false;
                        } else {
                            $protocolLine['vk'] = false;
                        }
                        $protocolLine['serial_number'] = (int)$lineData[0];
                        $protocolLine['lastname'] = $lineData[1];
                        $protocolLine['firstname'] = $lineData[2];
                        $protocolLine['club'] = implode(' ', array_slice($lineData, 3, $fieldsCount - $indent - 2));

                        $linesList->push($protocolLine);
                    }
                }
            }

            return $linesList;
        } catch (Exception $e) {
            throw new ParsingException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
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
            } elseif ($place === 'в/к') {
                $indent++;
                $this->setVk = true;
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
            } elseif (preg_match('#\d\d.\d\d#', $time)) {
                $indent++;
                $time = null;
            } else {
                $time = null;
            }

            return $time;
        }
        if ($column === 'runner_number') {
            return (int)$lineData[$fieldsCount - $indent++];
        }
        if ($column === 'rank') {
            $rank = $lineData[$fieldsCount - $indent];
            if (preg_match('#^[КМСCKMIбр\/юЮБРкмсkmc]{1,4}$#s', $rank) || in_array($rank, ['МСМК', 'КМС', 'б/р'], true)) {
                $indent++;
                return $rank;
            } else {
                return '';
            }
        }
        if ($column === 'year') {
            $year = $lineData[$fieldsCount - $indent];
            if (is_numeric($year) && preg_match('#\d{4}#', $year)) {
                $indent++;
                return (int)$year;
            } else {
                return null;
            }
        }
        return null;
    }

    private function getColumn(string $field): ?string
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
        if (str_contains($field, 'ставан')) {
            return '';
        }
        return null;
    }

    public function check(UploadedFile $file): bool
    {
        $content = $file->get();
        return (bool)preg_match('#<h2>\w{3}</h2><pre\>\w+|<br />#u', $content);
    }
}
