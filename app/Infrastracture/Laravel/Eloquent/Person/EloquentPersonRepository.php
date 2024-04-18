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
        return Person::find($id);
    }

    public function lockById(int $id): ?Person
    {
        return Person::lockForUpdate()->find($id);
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
        $query = Person::orderBy('lastname');

        if ($criteria->hasParam('clubId')) {
            $query
                ->where('club_id', $criteria->param('clubId'))
            ;
        }

        if ($criteria->hasParam('input')) {
            /** @var PersonInfo $info */
            $info = $criteria->param('input');

            $query
                ->where('lastname', $info->lastname)
                ->where('firstname', $info->firstname)
                ->where('birthday', $info->birthday)
            ;
        }

        dd($query);
        return $query->get();
    }

    public function oneByCriteria(Criteria $criteria): ?Person
    {
        return $this->byCriteria($criteria)->first();
    }
}
