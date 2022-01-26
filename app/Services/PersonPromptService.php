<?php

namespace App\Services;

use App\Models\Person;
use App\Models\PersonPrompt;
use App\Repositories\PersonPromptRepository;

class PersonPromptService
{
    public function __construct(private PersonPromptRepository $repository)
    {}

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


    public function deletePrompt(string $prompt): void
    {
        PersonPrompt::wherePrompt($prompt)->delete();
    }

    /**
     * В ответе [prepared_line => person_id, ]
     *
     * @return array<string, int>
     */
    public function identPersonsByPrompts(array $preparedLines): array
    {
         $prompts = $this->repository->findPersonsPrompts($preparedLines);
         return $prompts->pluck('person_id', 'prompt')->toArray();
    }
}
