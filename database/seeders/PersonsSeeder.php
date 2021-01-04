<?php

namespace Database\Seeders;

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
        $personsList = Storage::get('bfo.csv');
        foreach (explode(PHP_EOL, $personsList) as $index => $personLine) {
            $personData = str_getcsv($personLine, ';');

            if (!isset($personData[1])) {
                continue;
            }

            $lastname = $personData[1];
            $firstname = $personData[2];
            if ($index === 0 || $index === 1 || empty($lastname) || empty($firstname)) {
                continue;
            }
            $person = new Person();
            $lastname = mb_convert_encoding($lastname, "utf-8", "windows-1251");
            $firstname = mb_convert_encoding($firstname, "utf-8", "windows-1251");
            $person->lastname = $lastname;
            $person->firstname = $firstname;
            $person->patronymic = empty($personData[3]) ? null : mb_convert_encoding($personData[3], "utf-8", "windows-1251");
            try {
                $birthday = Carbon::createFromFormat('d.m.Y', $personData[4]);
                $person->birthday = $birthday;
            } catch (\Exception) {
                try {
                    $birthday = Carbon::createFromFormat('Y', $personData[4]);
                    $birthday->setDay(1);
                    $birthday->setMonth(1);
                    $person->birthday = $birthday;
                } catch (\Exception) {
                    $person->birthday = null;
                }
            }
            $person->save();
        }
    }
}
