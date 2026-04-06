<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\PersonPrompt\PersonPrompt;
use Illuminate\Support\Collection;

final readonly class PersonPromptService
{
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
        $prompts = PersonPrompt::select()
            ->join('person', 'person.id', '=', 'persons_prompt.person_id')
            ->whereIn('persons_prompt.prompt', $preparedLines)
            ->where('person.active', true)
            ->get()
        ;

        return $prompts->pluck('person_id', 'prompt')->toArray();
    }

    /**
     * @return Collection|PersonPrompt[]
     */
    public function all(): Collection
    {
        return PersonPrompt::select('persons_prompt.person_id', 'persons_prompt.prompt', 'persons_prompt.metaphone')
            ->distinct()
            ->join('person', 'person.id', '=', 'persons_prompt.person_id')
            ->where('person.active', true)
            ->get()
        ;
    }
}
