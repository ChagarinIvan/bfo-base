<?php

namespace App\Services;

use App\Models\Person;
use App\Models\PersonPrompt;
use App\Repositories\PersonPromptRepository;

class PersonPromptService
{
    public function __construct(private readonly PersonPromptRepository $repository)
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

    public function deletePersonPrompt(int $promptId): void
    {
        PersonPrompt::destroy($promptId);
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

    public function fillPrompt(PersonPrompt $prompt, array $formParams, int $personId = null): PersonPrompt
    {
        $prompt->fill($formParams);
        if ($personId) {
            $prompt->person_id = $personId;
        }

        return $prompt;
    }

    public function storePersonPrompt(PersonPrompt $prompt): PersonPrompt
    {
        $prompt->save();

        return $prompt;
    }

    public function getPrompt(int $promptId): PersonPrompt
    {
        $prompt = PersonPrompt::find($promptId);
        if ($prompt) {
            return $prompt;
        }
        throw new \RuntimeException('Wrong prompt id.');
    }
}
