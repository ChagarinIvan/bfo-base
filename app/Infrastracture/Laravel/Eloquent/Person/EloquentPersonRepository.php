<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Person;

use App\Domain\Person\Person;
use App\Domain\Person\PersonInfo;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

final class EloquentPersonRepository implements PersonRepository
{
    public function byId(int $id): ?Person
    {
        return Person::where('active', true)->find($id);
    }

    public function lockById(int $id): ?Person
    {
        return Person::where('active', true)->lockForUpdate()->find($id);
    }

    public function add(Person $person): void
    {
        $person->create();
    }

    public function update(Person $person): void
    {
        $person->save();
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        $query = Person::where('person.active', true)
            ->select('person.*')
            ->orderBy('person.lastname')
        ;

        if ($criteria->hasParam('clubId')) {
            $query->where('person.club_id', $criteria->param('clubId'));
        }

        if ($criteria->hasParam('year')) {
            $query->where('person.year', $criteria->param('year'));
        }

        if ($criteria->hasParam('withoutLines')) {
            $query
                ->leftjoin('protocol_lines', 'protocol_lines.person_id', '=', 'person.id')
                ->whereNull('protocol_lines.id')
            ;
        }

        if ($criteria->hasParam('info')) {
            /** @var PersonInfo $info */
            $info = $criteria->param('info');

            $query
                ->where('person.lastname', $info->lastname)
                ->where('person.firstname', $info->firstname)
                ->where('person.birthday', $info->birthday)
                ->where('person.citizenship', $info->citizenship)
            ;
        }

        return $query->get();
    }

    public function oneByCriteria(Criteria $criteria): ?Person
    {
        return $this->byCriteria($criteria)->first();
    }
}
