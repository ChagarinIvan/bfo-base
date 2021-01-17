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

            if ($index === 0 || !isset($personData[0]) || $personData[0] === null) {
                continue;
            }

            $person = explode(' ', $personData[0]);
            $lastname = $person[0] ?? '';
            $firstname = $person[1] ?? '';

            if (empty($lastname) || empty($firstname)) {
                continue;
            }

            $person = new Person();
            $lastname = mb_convert_encoding($lastname, "utf-8", "windows-1251");
            $firstname = mb_convert_encoding($firstname, "utf-8", "windows-1251");
            $person->lastname = $lastname;
            $person->firstname = $firstname;
            $person->prompt = '[]';
            try {
                if ($personData[9] !== '0') {
                    $birthday = Carbon::createFromFormat('Y', $personData[9]);
                    $person->birthday = $birthday;
                } else {
                    $person->birthday = null;
                }
            } catch (\Exception) {
                $person->birthday = null;
            }
            $person->save();
        }
    }
}
