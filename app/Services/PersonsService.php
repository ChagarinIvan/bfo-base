<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Person;
use Illuminate\Support\Collection;

class PersonsService
{
    public function __construct(private PersonPromptService $promptService)
    {}

    /**
     * @return Person[]|Collection
     */
    public function allPersons(): Collection
    {
        return Person::all();
    }

    public function getPerson(int $personId): Person
    {
        $person = Person::find($personId);
        if ($person) {
            return $person;
        }
        throw new \RuntimeException('Wrong person id.');
    }

    public function getPersons(Collection $personsIds): Collection
    {
        return Person::whereIn('id', $personsIds)->get();
    }

    public function storePerson(Person $person): Person
    {
        $person->save();
        $this->makePrompts($person);
        return $person;
    }

    public function makePrompts(Person $person): void
    {
        $existPrompts = $person->prompts->pluck('prompt')->toArray();
        $prompts = [];

        $hasNameSake = Person::whereFirstname($person->firstname)->whereLastname($person->lastname)->count() > 1;

        $personData = [
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->lastname)),
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->firstname)),
        ];

        $reversPersonData = [
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->firstname)),
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->lastname)),
        ];

        if ($hasNameSake) {
            $this->promptService->deletePrompt(implode('_', $personData));
            $this->promptService->deletePrompt(implode('_', $reversPersonData));
        } else {
            $prompts[] = implode('_', $personData);
            $prompts[] = implode('_', $reversPersonData);
        }

        if ($person->birthday !== null) {
            $personData[] = $person->birthday->format('Y');
            $reversPersonData[] = $person->birthday->format('Y');
            $prompts[] = implode('_', $personData);
            $prompts[] = implode('_', $reversPersonData);
        }

        $prompts = array_diff($prompts, $existPrompts);
        foreach ($prompts as $prompt) {
            $this->promptService->storePrompt($prompt, $person->id);
        }
    }

    public function updatePerson(Person $person, array $personData): Person
    {
        $this->promptService->deletePrompts($person);
        $person = $this->fillPerson($person, $personData);
        return $this->storePerson($person);
    }

    public function fillPerson(Person $person, array $personData): Person
    {
        $person->fill($personData);
        if ($person->club_id === 0) {
            $person->club_id = null;
        }
        return $person;
    }

    /**
     * @param int $clubId
     * @return Collection|Person[]
     */
    public function getClubPersons(int $clubId): Collection
    {
        return Person::whereClubId($clubId)->get();
    }
}
