<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use App\Filters\RanksFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

final readonly class RanksRepository
{
    /**
     * Выборка действующих актуальных разрядов с фильтром по типу разряда
     */
    public function getRanksList(RanksFilter $filter): Collection
    {
        /** @var Rank|Builder $ranks */
        $ranks = Rank::selectRaw('ranks.*')
            ->leftJoin('events', 'events.id', '=', 'ranks.event_id');

        if ($filter->isOrderDescByFinishDate) {
            $ranks->orderByRaw('ranks.finish_date DESC, events.date DESC');
        } elseif ($filter->isOrderByFinish) {
            $ranks->orderByRaw('ranks.finish_date');
        }

        if ($filter->personId !== null) {
            $ranks->where('ranks.person_id', $filter->personId);
        }
        if ($filter->rank !== null) {
            $ranks->where('ranks.rank', $filter->rank);
        }
        if ($filter->startDateLess !== null) {
            $ranks->where('start_date', '<=', $filter->startDateLess);
        }
        if ($filter->startDateMore !== null) {
            $ranks->where('start_date', '>', $filter->startDateMore);
        }
        if ($filter->finishDateMore !== null) {
            $ranks->where('finish_date', '>=', $filter->finishDateMore);
        }
        if ($filter->finishDateLess !== null) {
            $ranks->where('finish_date', '<', $filter->finishDateLess);
        }
        if ($filter->haveNoNextRank) {
            $ranks->where(new Expression("
                (SELECT COUNT(*)
                FROM ranks AS t1
                WHERE t1.person_id = ranks.person_id
                AND ranks.finish_date <= t1.start_date
                )
            "), '=', 0);
        }

        if ($filter->with !== null) {
            $ranks->with($filter->with);
        }

        return $ranks->get();
    }

    public function getPersonsIdsWithoutRanks(): Collection
    {
        return Person::where('person.active', true)
            ->selectRaw('person.id')
            ->join('ranks', 'ranks.person_id', '=', 'person.id', 'left outer')
            ->get()
        ;
    }
}
