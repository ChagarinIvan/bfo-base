<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Person;
use App\Models\PersonPrompt;
use App\Services\IdentService;
use Illuminate\Database\Seeder;

class PersonPromptsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach (Person::all() as $person) {
            $person->makePrompts();

            $oldPrompt = $person->prompt;
            if ($oldPrompt !== '[]' && is_array($oldPrompt)) {
                foreach ($oldPrompt as $personLine) {
                    $personLine = IdentService::prepareLine($personLine);
                    $prompt = new PersonPrompt();
                    $prompt->person_id = $person->id;
                    $prompt->prompt = $personLine;
                    $prompt->save();
                }
            }
        }
    }
}
