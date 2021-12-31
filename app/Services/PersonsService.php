<?php

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
        $personData = [
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->lastname)),
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->firstname)),
        ];

        $reversPersonData = [
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->firstname)),
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->lastname)),
        ];

        $this->promptService->storePrompt(implode('_', $personData), $person->id);
        $this->promptService->storePrompt(implode('_', $reversPersonData), $person->id);

        if ($person->birthday !== null) {
            $personData[] = $person->birthday->format('Y');
            $reversPersonData[] = $person->birthday->format('Y');
            $this->promptService->storePrompt(implode('_', $personData), $person->id);
            $this->promptService->storePrompt(implode('_', $reversPersonData), $person->id);
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
