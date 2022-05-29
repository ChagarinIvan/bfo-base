<?php

namespace App\Models\Parser;

use App\Models\Rank;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HrodnoParser extends AbstractParser
{
    /**
     * @param string $file
     * @param bool $needConvert
     * @return Collection
     */
    public function parse(string $file, bool $needConvert = true): Collection
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($file);
        $xpath = new DOMXpath($doc);
        $groupAnchorNodes = @$xpath->query('//a[@id]');
        $linesList = new Collection();
        foreach ($groupAnchorNodes as $node) {
            $groupDescriptionsNodes = @$xpath->query('following-sibling::table[1]//td', $node);
            $columnsNodes = @$xpath->query('following-sibling::table[2]//th', $node);
            $linesNodes = @$xpath->query('following-sibling::table[3]//tr', $node);
            $groupName = '';
            $distanceLength = 0;
            $distancePoints = 0;
            if ($groupDescriptionsNodes->length > 0) {
                if (
                    ($groupNameNode = $groupDescriptionsNodes->item(0))
                    && preg_match('#^Группа\s([^\s]+)\s+\(#', $groupNameNode->nodeValue, $m)
                ) {
                    $groupName = $m[1];
                }

                if (
                    ($distanceNode = $groupDescriptionsNodes->item(1))
                    && preg_match('#^([^\s]+)\s+km\s+([^\s])\s+m#', $distanceNode->nodeValue, $m)
                ) {
                    $distanceLength = floatval(str_replace(',', '.', $m[1])) * 1000;
                }

                if (
                    ($pointsNode = $groupDescriptionsNodes->item(2))
                    && preg_match('#^(\d+)\s+C#', $pointsNode->nodeValue, $m)
                ) {
                    $distancePoints = (int)$m[1];
                }
            }

            $serialNumber = 1;
            foreach ($linesNodes as $lineNode) {
                $protocolLine = [
                    'group' => $groupName,
                    'serial_number' => $serialNumber,
                    'runner_number' => 0,
                    'distance' => [
                        'length' => $distanceLength,
                        'points' => $distancePoints,
                    ],
                ];
                $empty = false;
                foreach ($columnsNodes as $index => $columnNode) {
                    $columnName = $this->getColumn($columnNode->nodeValue);
                    if ($columnName === null) {
                        continue;
                    }
                    $valueNode = @$xpath->query('td['.($index + 1).']', $lineNode);
                    if ($valueNode->length === 0) {
                        $empty = true;
                        break;
                    }
                    $lineData = $valueNode->item(0)->nodeValue;
                    if (str_contains($lineData, 'ласс дистанции') || str_contains($lineData, 'Ранг не определялся')) {
                        break(2);
                    }
                    $protocolLine = $this->getValue($protocolLine, $columnName, $lineData);
                }

                if (!$empty) {
                    $serialNumber++;
                    $linesList->push($protocolLine);
                }
            }
        }

        return $linesList;
    }

    private function getColumn(string $columnNode): ?string
    {
        $field = mb_strtolower($columnNode);
        if (str_contains($field, 'чки')) {
            return 'points';
        }
        if (str_contains($field, 'амилия')) {
            return 'lastname';
        }
        if (str_contains($field, 'ып')) {
            return 'complete_rank';
        }
        if (str_contains($field, 'есто')) {
            return 'place';
        }
        if (str_contains($field, 'ремя')) {
            return 'time';
        }
        if ($field ==='гр' || $field === 'г.р.') {
            return 'year';
        }
        if (str_contains($field, 'омер')) {
            return 'runner_number';
        }
        if (str_contains($field, 'азряд')) {
            return 'rank';
        }
        if (str_contains($field, 'луб')) {
            return 'club';
        }
        return null;
    }

    private function getValue(array $protocolLine, string $column, string $lineData): array
    {
        $value = null;
        switch ($column) {
            case 'place':
            case 'points':
                $value = is_numeric($lineData) ? (int)$lineData: $value;
                break;
            case 'rank':
            case 'complete_rank':
                if (Rank::validateRank($lineData)) {
                    $value = $lineData;
                }
                break;
            case 'lastname':
                [$value, $name] = explode(' ', $lineData);
                $protocolLine['firstname'] = $name;
                break;
            case 'club':
                $value = trim($lineData, ' -');
                break;
            case 'runner_number':
            case 'serial_number':
                $value = (int)$lineData;
                break;
            case 'year':
                $value = (int)((int)$lineData > 20 ? "19{$lineData}" : "20{$lineData}");
                break;
            case 'time':
                try {
                    if (strlen($lineData) === 5) {
                        $lineData = "00:{$lineData}";
                    } elseif (strlen($lineData) === 6) {
                        $lineData = "0{$lineData}";
                    }

                    $value = Carbon::createFromTimeString($lineData);
                } catch (Exception) {}
                break;
        }

        $protocolLine[$column] = $value;
        return $protocolLine;
    }

    public function check(string $file, string $extension): bool
    {
        if ($extension === 'html') {
            return str_contains($file, 'www.sportsoftware.de');
        }

        return false;
    }
}
