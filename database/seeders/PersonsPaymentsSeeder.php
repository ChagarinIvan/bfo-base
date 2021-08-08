<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\PersonPayment;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PersonsPaymentsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->executeBase(2021, 10);
        $this->executeBase(2020, 12);
    }

    private function executeBase(int $year, int $column): void
    {
        $payments = [];
        echo "Start sync {$year} year!".PHP_EOL;

        $csvBase = Storage::get("{$year}.csv");
        $list = explode(PHP_EOL, $csvBase);

        foreach ($list as $index => $personLine) {
            $personData = str_getcsv($personLine);

            if ($index === 0 || !isset($personData[0]) || $personData[0] === null) {
                continue;
            }

            $person = explode(' ', $personData[0]);
            $lastname = $person[0] ?? '';
            $firstname = $person[1] ?? '';

            $birthday = Carbon::createFromFormat('Y', $personData[3]);
            if ($birthday instanceof Carbon) {
                $birthday->startOfYear();
            } else {
                continue;
            }

            $paymentDate = $personData[$column];

            if (empty($lastname) || empty($firstname) || empty($paymentDate)) {
                continue;
            }

            echo $paymentDate.PHP_EOL;
            $paymentDate = Carbon::createFromFormat('d.m.Y', substr($paymentDate, 0, 10));
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
            } else {
                $person = $persons->first();
            }

            $payment = new PersonPayment();
            $payment->person_id = $person->id;
            $payment->year = $year;
            $payment->date = $paymentDate;
            $payments[] = $payment;
        }

        foreach ($payments as $payment) {
            $payment->save();
        }
    }
}