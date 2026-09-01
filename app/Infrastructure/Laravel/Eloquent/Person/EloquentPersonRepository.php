<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person;

use App\Domain\Person\Person;
use App\Domain\Person\PersonInfo;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Database\Eloquent\Builder;
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
            ->with('payments')
            ->orderBy('person.lastname')
        ;

        if ($criteria->hasParam('ids')) {
            $query->whereIn('person.id', $criteria->param('ids'));
        }

        if ($criteria->hasParam('clubId')) {
            $query->where('person.club_id', $criteria->param('clubId'));
        }

        if ($criteria->hasParam('year')) {
            $query->where('person.birthday', 'LIKE', $criteria->param('year') . '%');
        }

        if ($criteria->hasParam('withoutLinesAndPayments')) {
            $query
                ->leftjoin('protocol_lines', 'protocol_lines.person_id', '=', 'person.id')
                ->whereNull('protocol_lines.id')
                ->leftjoin('persons_payments', 'persons_payments.person_id', '=', 'person.id')
                ->whereNull('persons_payments.id')
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

    /** @return Slice<Person> */
    public function paginate(Criteria $criteria): Slice
    {
        return new Slice(new EloquentQueryAdapter($this->createPaginatedQuery($criteria)));
    }

    /** @return Builder<Person> */
    private function createPaginatedQuery(Criteria $criteria): Builder
    {
        $query = Person::query()
            ->where('person.active', true)
            ->select('person.*')
            ->orderBy('person.lastname')
            ->orderBy('person.firstname')
            ->orderBy('person.id');

        if ($criteria->hasParam('clubId')) {
            $query
                ->join('club', 'club.id', '=', 'person.club_id')
                ->where('club.active', true)
                ->where('person.club_id', $criteria->param('clubId'))
            ;
        }

        return $query;
    }
}
