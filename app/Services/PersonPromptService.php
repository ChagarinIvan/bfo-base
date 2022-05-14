<?php

namespace App\Services;

use App\Models\PersonPrompt;
use App\Repositories\PersonPromptRepository;
use Illuminate\Support\Collection;
use Mav\Slovo\Phonetics;

class PersonPromptService
{
    public function __construct(private readonly PersonPromptRepository $repository)
    {}

    public function storePrompt(string $personLine, int $personId): PersonPrompt
    {
        $prompt = new PersonPrompt();
        $prompt->person_id = $personId;
        $prompt->prompt = $personLine;
        $this->storePersonPrompt($prompt);

        return $prompt;
    }

    public function makeMetaphone(string $personLine): string
    {
        $phonetics = new Phonetics();

        return $phonetics->metaphour($personLine);
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

    /**
     * @return Collection|PersonPrompt[]
     */
    public function all(): Collection
    {
        return PersonPrompt::all();
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
        $prompt->metaphone = $this->makeMetaphone($prompt->prompt);
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
