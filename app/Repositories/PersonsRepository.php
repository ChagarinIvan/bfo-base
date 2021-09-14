<?php

namespace App\Repositories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

class PersonsRepository
{
    /**
     * @return Collection|Person[]
     */
    public function getAll(): Collection
    {
        return Person::all();
    }

    public function getPersonsOrderedByProtocolLinesCountQuery(): Builder
    {
        return Person::join('protocol_lines', 'protocol_lines.person_id', '=', 'person.id')
            ->addSelect(new Expression('ANY_VALUE(person.id) AS id'))
            ->groupBy('protocol_lines.person_id')
            ->orderByRaw(new Expression('COUNT(protocol_lines.person_id) DESC'));
    }

    /**
     * @return Collection|Person[]
     */
    public function getPersons(Collection $ids, array $with): Collection
    {
        $personsQuery = Person::whereIn('id', $ids);

        if (count($with) > 0) {
            $personsQuery->with($with);
        }

        return $personsQuery->get();
    }
}
