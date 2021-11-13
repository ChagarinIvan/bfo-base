<?php

namespace App\Services;

use App\Models\Person;
use App\Models\PersonPrompt;
use App\Repositories\PersonPromptRepository;
use Illuminate\Support\Collection;

class PersonPromptService
{
    private PersonPromptRepository $repository;

    public function __construct(PersonPromptRepository $repository)
    {
        $this->repository = $repository;
    }

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

    /**
     * В ответе [prepared_line => person_id, ]
     * @param Collection $preparedLines
     * @return Collection
     */
    public function identPersonsByPrompts(Collection $preparedLines): Collection
    {
         $prompts = $this->repository->findPersonsPrompts($preparedLines);
         return $prompts->pluck('person_id', 'prompt');
    }
}
