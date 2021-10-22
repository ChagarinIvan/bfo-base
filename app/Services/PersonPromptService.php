<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Person;
use App\Models\PersonPrompt;

class PersonPromptService
{
    public function storePrompt(string $personLine, int $personId): PersonPrompt
    {
        $prompt = new PersonPrompt();
        $prompt->person_id = $personId;
        $prompt->prompt = $personLine;
        $prompt->save();
        return $prompt;
    }

    public function deletePrompts(Person $person): void
    {
        $person->prompts()->delete();
    }
}
