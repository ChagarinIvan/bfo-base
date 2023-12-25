<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Person;
use App\Models\PersonPayment;
use App\Services\PersonsService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use function explode;
use function str_getcsv;
use function substr;

class PersonsPaymentsSeeder extends Seeder
{
    private Filesystem $storage;
    private PersonsService $personService;

    public function __construct(Filesystem $storage, PersonsService $personService)
    {
        $this->storage = $storage;
        $this->personService = $personService;
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->executeBase(2021, 10);
        $this->executeBase(2020, 12);
    }

    private function executeBase(int $year, int $column): void
    {
        $payments = [];
        echo "Start sync {$year} year!" . PHP_EOL;

        $csvBase = $this->storage->get("{$year}.csv");
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

            echo $paymentDate . PHP_EOL;
            $paymentDate = Carbon::createFromFormat('d.m.Y', substr($paymentDate, 0, 10));
            $persons = Person::whereLastname($lastname)->whereFirstname($firstname)->whereBirthday($birthday)->get();

            if ($persons->isEmpty()) {
                echo 'newscomer:' . PHP_EOL;
                echo $lastname . ' ' . $firstname . PHP_EOL;
                $person = new Person();
                $person->lastname = $lastname;
                $person->firstname = $firstname;
                $person->birthday = $birthday;
                $this->personService->storePerson($person);
            } elseif ($persons->count() > 1) {
                echo 'ALARM!!!!' . PHP_EOL;
                echo $lastname . ' ' . $firstname . PHP_EOL;
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
