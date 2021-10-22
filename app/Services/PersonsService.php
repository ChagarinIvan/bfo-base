<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Person;
use App\Repositories\PersonsRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PersonsService
{
    private PersonsRepository $personsRepository;
    private PersonPromptService $promptService;

    public function __construct(PersonsRepository $personsRepository, PersonPromptService $promptService)
    {
        $this->personsRepository = $personsRepository;
        $this->promptService = $promptService;
    }

    public function getMostParticipantPersonPaginator(string $search): LengthAwarePaginator
    {
        $query = $this->personsRepository->getPersonsOrderedByProtocolLinesCountQuery();

        if (strlen($search) > 0) {
            $query->where('person.firstname', 'LIKE', '%'.$search.'%')
                ->orWhere('person.lastname', 'LIKE', '%'.$search.'%');
        }
        return $query->paginate(12);
    }

    /**
     * @return Person[]|Collection
     */
    public function allPersons(): Collection
    {
        return $this->personsRepository->getAll();
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
            IdentService::prepareLine(mb_strtolower($person->lastname)),
            IdentService::prepareLine(mb_strtolower($person->firstname)),
        ];

        $reversPersonData = [
            IdentService::prepareLine(mb_strtolower($person->firstname)),
            IdentService::prepareLine(mb_strtolower($person->lastname)),
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
}
