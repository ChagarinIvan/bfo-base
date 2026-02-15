<?php

declare(strict_types=1);

namespace App\Models\Parser;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use function count;
use function file_put_contents;
use function mb_convert_case;
use function mb_strtolower;
use function str_contains;
use function str_replace;
use function sys_get_temp_dir;
use function tempnam;
use function trim;

class SportOrgParser extends AbstractParser
{
    public function parse(string $file): Collection
    {
        if (!preg_match('/var\s+race\s*=\s*(\{.*?\});/s', $file, $m1)) {
            return collect();
        }

        Log::info('matched');
        preg_match('/var\s+Qualification\s*=\s*(\{.*?\});/s', $file, $m2);
        $qualifications = rtrim($m2[1], ';');
        // заменить одинарные кавычки на двойные
        $qualifications = str_replace("'", '"', $qualifications);
        // ключи-числа обернуть в кавычки
        $qualifications = preg_replace('/(\{|,)\s*(\d+)\s*:/', '$1 "$2":', $qualifications);
        $qualifications = json_decode($qualifications, true);
        $qualifications = collect($qualifications);

        $json = $m1[1];
        $json = rtrim($json, ';');
        $race = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $persons = collect($race['persons'])->keyBy('id');
        $courses = collect($race['courses'])->keyBy('id');
        $clubs = collect($race['organizations'])->keyBy('id');
        $groups = collect($race['groups'])->keyBy('id');
        $results = collect($race['results'])->groupBy(function (array $item) use ($persons) {
            return $persons->get($item['person_id'])['group_id'];
        });

        $linesList = new Collection();

        foreach ($groups as $group) {
            $serialNumber = 1;
            foreach ($results->get($group['id']) as $result) {
                $person = $persons->get($result['person_id']);
                $group = $groups->get($person['group_id']);
                $course = $courses->get($group['course_id']);
                $club = $clubs->get($person['organization_id']);

                try {
                    $time = Carbon::createFromTimeString($result['result']);
                } catch (Exception) {
                    $time = null;
                }

                $protocolLine = [
                    'group' => $group['name'],
                    'distance' => [
                        'length' => $course['length'],
                        'points' => count($course['controls']),
                    ],
                    'time' => $time,
                    'year' => $person['year'],
                    'place' => $result['place'] > 0 ? $result['place'] : null,
                    'vk' => $person['is_out_of_competition'],
                    'serial_number' => $serialNumber++,
                    'runner_number' => $person['bib'],
                    'rank' => $qualifications->get($person['qual']),
                    'lastname' => $person['surname'],
                    'firstname' => $person['name'],
                    'complete_rank' => ($rank = $qualifications->get($result['assigned_rank'])) === 'б/р' ? null : $rank,
                    'club' => $club['name'],
                    'info' => $result['status_comment'],
                ];

                $linesList->push($protocolLine);
            }
        }

        return $linesList;
    }

    public function check(string $file, string $extension): bool
    {
        return str_contains($file, 'sportorg-table');
    }

    private function getContent(string $file): array
    {
        $fileName = tempnam(sys_get_temp_dir(), 'TMP_');
        file_put_contents($fileName, $file);
        $xlsx = new Xlsx();

        try {
            $spreadsheet = $xlsx->load($fileName);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception) {
            return [];
        }

        return $spreadsheet->getActiveSheet()->toArray();
    }

    private function getColumn(string $field): string
    {
        $field = mb_strtolower($field);
        if ($field === 'classname') {
            return 'group';
        } elseif ($field === 'startnumber') {
            return 'runner_number';
        } elseif ($field === 'place') {
            return 'place';
        } elseif ($field === 'name') {
            return 'firstname';
        } elseif ($field === 'surname') {
            return 'lastname';
        } elseif ($field === 'result') {
            return 'time';
        } elseif ($field === 'club') {
            return 'club';
        } elseif ($field === 'birthyear') {
            return 'year';
        } elseif ($field === 'distance') {
            return 'distance';
        }

        return '';
    }

    private function getValue(string $column, string $columnData): mixed
    {
        $columnData = trim($columnData);
        switch ($column) {
            case 'time':
                try {
                    $time = Carbon::createFromTimeString($columnData);
                } catch (Exception) {
                    $time = null;
                }
                return $time;
            case 'place':
            case 'runner_number':
            case 'year':
                return $columnData ? (int)$columnData : null;
            case 'distance':
                return $columnData ? ((int)$columnData * 1000) : null;
            case 'firstname':
            case 'lastname':
                return mb_convert_case($columnData, MB_CASE_TITLE);
            case 'club':
                return $columnData;
            case 'group':
                return str_replace(['Жанчыны', 'Mужчыны', 'Дзяўчыны', 'Хлопцы'], ['Ж', 'М', 'Ж', 'М'], $columnData);
        }
        return null;
    }
}
