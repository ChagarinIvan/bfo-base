<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person;

use App\Domain\Person\Person;
use App\Domain\Person\PersonInfo;
use App\Domain\Person\PersonRankHistory;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\PersonResources;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use function mb_strtolower;
use function strtr;

final class EloquentPersonRepository implements PersonRepository
{
    public function byId(int $id, PersonResources $resources = new PersonResources()): ?Person
    {
        $query = Person::where('active', true);

        if ($resources->protocolLines) {
            $query->with('protocolLines.distance.event.competition', 'protocolLines.distance.group');
        }

        if ($resources->rankHistory) {
            $query->with(['rankHistories' => static function (Relation $relation): void {
                $relation->getQuery()
                    ->with('protocolLine.distance.event.competition')
                    ->orderBy('achieved_on')
                    ->orderBy('id')
                ;
            }]);
        }

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

        if ($criteria->hasParam('rankId')) {
            $query->where('person.current_rank', $criteria->param('rankId'));
        }

        if ($criteria->hasParam('rankFinishedBefore')) {
            $query->where('person.current_rank_finished_on', '<', $criteria->param('rankFinishedBefore'));
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
        $first = $this->byCriteria($criteria)->first();

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
