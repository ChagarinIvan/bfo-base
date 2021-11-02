<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Person;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    private Filesystem $storage;

    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $personsList = $this->storage->get('bfo.csv');
        $clubs = Club::all();
        $clubs = $clubs->keyBy('name');
        foreach (explode(PHP_EOL, $personsList) as $index => $personLine) {
            $personData = str_getcsv($personLine, ';');

            if ($index === 0 || !isset($personData[0]) || $personData[0] === null) {
                continue;
            }

            $person = explode(' ', $personData[0]);
            $lastname = $person[0] ?? '';
            $firstname = $person[1] ?? '';

            if (empty($lastname) || empty($firstname)) {
                continue;
            }

            $clubName = mb_convert_encoding($personData[6], "utf-8", "windows-1251");
            if (empty($clubName)) {
                continue;
            }

            preg_match('#(.+)\s\[\d+\]#', $clubName, $match);
            if (empty($match)) {
                continue;
            }

            $clubName = $match[1];
            if ($clubs->has($clubName)) {
                $club = $clubs->get($clubName);
            } else {
                $club = new Club();
                $club->name = $clubName;
                $club->save();
                $clubs[$clubName] = $club;
            }

            /** @var Person $person */
            $person = Person::whereLastname(mb_convert_encoding($lastname, "utf-8", "windows-1251"))
                ->whereFirstname(mb_convert_encoding($firstname, "utf-8", "windows-1251"))
                ->first();

            $person->club_id = $club->id;
            $person->save();
        }
    }
}
