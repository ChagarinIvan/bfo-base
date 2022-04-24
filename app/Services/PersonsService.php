<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PersonsService
{
    private const SORT_BY_COLUMNS = [
        'fio',
        'events_count',
        'club_name',
        'birthday',
    ];

    public function __construct(
        private readonly PersonPromptService $promptService
    ) {}

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

    /**
     * Выборка для страницы спортсменов для фронтенд апи с пагинацией и уже преобразованными полями.
     */
    public function getPersonsList(string $sortBy, int $sortMode, string $search): Builder
    {
        $sortBy = in_array($sortBy, self::SORT_BY_COLUMNS, true) ? $sortBy : 'fio';
        $sortMode = $sortMode === 1 ? 'DESC' : 'ASC';

        $persons = Person::withCount('protocolLines')->with('club');

        $persons = match ($sortBy) {
            'fio' => $persons->orderBy('lastname', $sortMode)->orderBy('firstname', $sortMode),
            'club_name' => $persons->join('club', 'person.club_id', '=', 'club.id')->orderBy('club.name', $sortMode),
            'events_count' => $persons->orderBy('protocol_lines_count', $sortMode),
            'birthday' => $persons->orderBy('birthday', $sortMode),
        };

        if ($search) {
            $persons = $persons->where('firstname', 'like', "%{$search}%")
                ->orWhere('lastname', 'like', "%{$search}%")
                ->orWhereHas('club', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orWhere('birthday', 'like', "%{$search}%");
        }

        return $persons;
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

    public function updatePerson(int $personId, array $personData): Person
    {
        $person = $this->getPerson($personId);
//        $this->promptService->deletePrompts($person);
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

    public function deletePerson(int $personId): void
    {
        $person = $this->getPerson($personId);
        $protocolLines = ProtocolLine::wherePersonId($personId)->get();
        $protocolLines->each(function (ProtocolLine $line) {
            $line->person_id = null;
            $line->save();
        });
        $person->delete();
    }
}
