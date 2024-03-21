<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\PersonPrompt\PersonPrompt;
use Illuminate\Support\Collection;
use Mav\Slovo\Phonetics;
use RuntimeException;

class PersonPromptService
{
    public function __construct(
        private readonly Phonetics $phonetics
    ) {
    }

    public function storePrompt(string $personLine, int $personId): PersonPrompt
    {
        $prompt = new PersonPrompt();
        $prompt->person_id = $personId;
        $prompt->prompt = $personLine;
        $this->storePersonPrompt($prompt);

        return $prompt;
    }

    public function changePromptForLine(string $preparedLine, int $personId): void
    {
        //меняем person_id для имеющихся таких же идентификаторов
        $prompts = PersonPrompt::wherePrompt($preparedLine)->get();
        if ($prompts->count() > 0) {
            foreach ($prompts as $prompt) {
                $prompt->person_id = $personId;
                $prompt->save();
            }
        } else {
            //создаём новый промпт
            $prompt = new PersonPrompt();
            $prompt->person_id = $personId;
            $prompt->prompt = $preparedLine;
        }
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
        $prompts = PersonPrompt::whereIn('prompt', $preparedLines)->get();

        return $prompts->pluck('person_id', 'prompt')->toArray();
    }

    /**
     * @return Collection|PersonPrompt[]
     */
    public function all(): Collection
    {
        return PersonPrompt::select('person_id', 'prompt', 'metaphone')->get();
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
        $prompt->metaphone = $this->phonetics->metaphour($prompt->prompt);
        $prompt->save();

        return $prompt;
    }

    public function getPrompt(int $promptId): PersonPrompt
    {
        $prompt = PersonPrompt::find($promptId);
        if ($prompt) {
            return $prompt;
        }
        throw new RuntimeException('Wrong prompt id.');
    }
}
