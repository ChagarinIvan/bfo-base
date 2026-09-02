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
use App\Infrastructure\Laravel\Eloquent\Person\PersonRankHistoryRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

final class EloquentPersonRepository implements PersonRepository
{
    public function byId(int $id, PersonResources $resources = new PersonResources()): ?Person
    {
        $query = Person::where('active', true);

        if ($resources->protocolLines) {
            $query->with('protocolLines.distance.event.competition', 'protocolLines.distance.group');
        }

        if ($resources->rankHistory) {
            $query->with(['rankHistoryRecords' => static function (Relation $relation): void {
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

        PersonRankHistoryRecord::query()->where('person_id', $person->id)->delete();

        foreach ($history as $row) {
            /** @var PersonRankHistory $row */
            PersonRankHistoryRecord::query()->create([
                'person_id' => $person->id,
                'protocol_line_id' => $row->protocolLineId,
                'distance_id' => $row->distanceId,
                'event_id' => $row->eventId,
                'competition_id' => $row->competitionId,
                'rank' => $row->rank->value,
                'change_type' => $row->changeType->value,
                'achieved_on' => $row->achievedOn,
                'activated_on' => $row->activatedOn,
                'started_on' => $row->startedOn,
                'finished_on' => $row->finishedOn,
            ]);
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

        if ($criteria->hasParam('rankId')) {
            $query->where('person.current_rank', $criteria->param('rankId'));
        }

        return $query;
    }
}
