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
        $persons = str_getcsv($personsList);
        foreach ($persons as $personData) {
            $person = new Person();
            $person->lastname = $personData[1];
            $person->firstname = $personData[2];
            $person->patronymic = empty($personData[3]) ? null : $personData[3];
            try {
                $birthday = Carbon::createFromTimeString($personData[4]);
                $person->birthday = $birthday;
            } catch (\Exception) {
                $person->birthday = null;
            }
            $person->save();
        }
    }
}
