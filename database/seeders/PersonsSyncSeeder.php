<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PersonsSyncSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $personsList = Storage::get('base.csv');
        $list = explode(PHP_EOL, $personsList);

        foreach ($list as $index => $personLine) {
            $personData = str_getcsv($personLine);

            if ($index === 0 || !isset($personData[0]) || $personData[0] === null) {
                continue;
            }

            $person = explode(' ', $personData[0]);
            $lastname = $person[0] ?? '';
            $firstname = $person[1] ?? '';

            $birthday = Carbon::createFromFormat('Y', $personData[2]);
            if ($birthday instanceof Carbon) {
                $birthday->startOfYear();
            } else {
                continue;
            }

            if (empty($lastname) || empty($firstname)) {
                continue;
            }

            $persons = Person::whereLastname($lastname)->whereFirstname($firstname)->whereBirthday($birthday)->get();
            if ($persons->isEmpty()) {
                echo 'newscomer:'.PHP_EOL;
                echo $lastname.' '.$firstname.PHP_EOL;
                $person = new Person();
                $person->lastname = $lastname;
                $person->firstname = $firstname;
                $person->birthday = $birthday;
                $person->save();
                $person->makePrompts();
            } elseif ($persons->count() > 1) {
                echo 'ALARM!!!!'.PHP_EOL;
                echo $lastname.' '.$firstname.PHP_EOL;
            }
        }
    }
}
