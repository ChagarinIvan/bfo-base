<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PersonsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $personsList = Storage::get('base.csv');
        foreach (explode(PHP_EOL, $personsList) as $index => $personLine) {
            $personData = str_getcsv($personLine, ';');

            if ($index === 0 || !isset($personData[0]) || $personData[0] === null) {
                continue;
            }

            $personData = explode(',', $personData[0]);
            $person = explode(' ', $personData[0]);
            $lastname = $person[0] ?? '';
            $firstname = $person[1] ?? '';

            if (empty($lastname) || empty($firstname)) {
                continue;
            }

            $person = new Person();
//            $lastname = mb_convert_encoding($lastname, "utf-8", "windows-1251");
//            $firstname = mb_convert_encoding($firstname, "utf-8", "windows-1251");
            $person->lastname = $lastname;
            $person->firstname = $firstname;
            $person->prompt = '[]';
            try {
                $birthday = Carbon::createFromFormat('Y', $personData[3]);
                $birthday->startOfYear();
                $person->birthday = $birthday;
            } catch (\Exception) {
                $person->birthday = null;
            }
            $clubName = $personData[1];
            $club = Club::whereName($clubName)->get();
            if ($club->count() > 0) {
                $person->club_id = $club->first()->id;
            }
            $person->save();
        }
    }
}
