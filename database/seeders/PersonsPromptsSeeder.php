<?php

namespace Database\Seeders;

use App\Models\PersonPrompt;
use App\Services\IdentService;
use App\Services\PersonsService;
use Illuminate\Database\Seeder;

/**
 * php artisan db:seed --class=PersonsPromptsSeeder
 */
class PersonsPromptsSeeder extends Seeder
{
    private PersonsService $personService;

    public function __construct(PersonsService $personService)
    {
        $this->personService = $personService;
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->personService->allPersons() as $person) {
            $prompts = [];
            $personData = [
                IdentService::prepareLine(mb_strtolower($person->lastname)),
                IdentService::prepareLine(mb_strtolower($person->firstname)),
            ];
            $prompts[] = implode('_', $personData);
            $reversPersonData = [
                IdentService::prepareLine(mb_strtolower($person->firstname)),
                IdentService::prepareLine(mb_strtolower($person->lastname)),
            ];
            $prompts[] = implode('_', $reversPersonData);

            if ($person->birthday !== null) {
                $personData[] = $person->birthday->format('Y');
                $prompts[] = implode('_', $personData);
                $reversPersonData[] = $person->birthday->format('Y');
                $prompts[] = implode('_', $reversPersonData);
            }

            foreach ($prompts as $promptLine) {
                if (PersonPrompt::where('prompt', $promptLine)->get()->count() === 0) {
                    $prompt = new PersonPrompt();
                    $prompt->person_id = $person->id;
                    $prompt->prompt = $promptLine;
                    $prompt->save();
                }
            }
        }
    }
}
