<?php

declare(strict_types=1);

namespace App\Models\Parser;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function count;
use function json_decode;
use function preg_match;
use function preg_replace;
use function rtrim;
use function str_contains;
use function str_replace;

class SportOrgParser extends AbstractParser
{
    public function parse(string $file): Collection
    {
        if (!preg_match('/var\s+race\s*=\s*(\{.*?\});/s', $file, $m1)) {
            return collect();
        }

        preg_match('/var\s+Qualification\s*=\s*(\{.*?\});/s', $file, $m2);
        $qualifications = rtrim($m2[1], ';');
        // заменить одинарные кавычки на двойные
        $qualifications = str_replace("'", '"', $qualifications);
        // ключи-числа обернуть в кавычки
        $qualifications = preg_replace('/(\{|,)\s*(\d+)\s*:/', '$1 "$2":', $qualifications);
        $qualifications = json_decode($qualifications, true, 512, JSON_THROW_ON_ERROR);
        $qualifications = collect($qualifications);

        $json = $m1[1];
        $json = rtrim($json, ';');
        $race = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $persons = collect($race['persons'])->keyBy('id');
        $courses = collect($race['courses'])->keyBy('id');
        $clubs = collect($race['organizations'])->keyBy('id');
        $groups = collect($race['groups'])->keyBy('id');
        $results = collect($race['results'])
            ->filter(static fn (array $item) => isset($item['person_id']))
            ->groupBy(static function (array $item) use ($persons) {
                return $persons->get($item['person_id'])['group_id'];
            })
        ;

        $linesList = new Collection();

        foreach ($groups as $group) {
            $serialNumber = 1;
            $groupResults = $results->get($group['id'])->sortBy('result');

            foreach ($groupResults as $result) {
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
}
