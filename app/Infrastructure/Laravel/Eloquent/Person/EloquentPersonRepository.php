<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person;

use App\Domain\Person\Person;
use App\Domain\Person\PersonInfo;
use App\Domain\Person\PersonRankHistory;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use function mb_strtolower;
use function strtr;

final class EloquentPersonRepository implements PersonRepository
{
    public function byId(int $id): ?Person
    {
        $query = Person::where('active', true);

        return $query->find($id);
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
        $history = $person->rankHistoryToPersist();

        if ($history === null) {
            return;
        }

        PersonRankHistory::query()->where('person_id', $person->id)->delete();

        foreach ($history as $row) {
            /** @var PersonRankHistory $row */
            $row->save();
        }
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->createCriteriaQuery($criteria)->get();
    }

    /** @return LazyCollection<int, int> */
    public function idsByCriteria(Criteria $criteria): LazyCollection
    {
        $query = Person::query()
            ->where('person.active', true)
            ->select('person.id')
            ->orderBy('person.id')
        ;

        if ($criteria->hasParam('rankFinishedBefore')) {
            $query->where('person.current_rank_finished_on', '<', $criteria->param('rankFinishedBefore'));
        }

        return $query->lazyById()->map(static fn (Person $person): int => $person->id);
    }

    public function oneByCriteria(Criteria $criteria): ?Person
    {
        /** @var Person|null $first */
        $first = $this->createCriteriaQuery($criteria)->first();

        return $first;
    }

    /** @return Slice<Person> */
    public function paginate(Criteria $criteria): Slice
    {
        return new Slice(new EloquentQueryAdapter($this->createPaginatedQuery($criteria)));
    }

    private function escapeLikePattern(string $value): string
    {
        return strtr($value, ['!' => '!!', '%' => '!%', '_' => '!_']);
    }

    /** @return Builder<Person> */
    private function createCriteriaQuery(Criteria $criteria): Builder
    {
        $query = Person::query()
            ->where('person.active', true)
            ->select('person.*')
            ->orderBy('person.lastname')
            ->orderBy('person.firstname')
            ->orderBy('person.id')
        ;

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

        if ($criteria->hasParam('firstname')) {
            $query->where('person.firstname', $criteria->param('firstname'));
        }

        if ($criteria->hasParam('lastname')) {
            $query->where('person.lastname', $criteria->param('lastname'));
        }

        return $query;
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

        if ($criteria->hasParam('ids')) {
            $query->whereIn('person.id', $criteria->param('ids'));
        }

        if ($criteria->hasParam('clubId')) {
            $query
                ->join('club', 'club.id', '=', 'person.club_id')
                ->where('club.active', true)
                ->where('person.club_id', $criteria->param('clubId'))
            ;
        }

        if ($criteria->hasParam('rankId')) {
            $query->where('person.current_rank', $criteria->param('rankId'));
        }

        if ($criteria->hasParam('name')) {
            $name = $this->escapeLikePattern(mb_strtolower((string) $criteria->param('name')));

            $pattern = '%' . $name . '%';
            $query->where(static function (Builder $query) use ($pattern): void {
                $query
                    ->whereRaw("LOWER(person.lastname) LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("LOWER(person.firstname) LIKE ? ESCAPE '!'", [$pattern])
                ;
            });
        }

        if ($criteria->hasParam('birthYear')) {
            $query->whereYear('person.birthday', (int) $criteria->param('birthYear'));
        }

        if ($criteria->hasParam('withoutLinesAndPayments')) {
            $query
                ->leftJoin('protocol_lines', 'protocol_lines.person_id', '=', 'person.id')
                ->whereNull('protocol_lines.id')
                ->leftJoin('persons_payments', 'persons_payments.person_id', '=', 'person.id')
                ->whereNull('persons_payments.id')
            ;
        }

        return $query;
    }
}
