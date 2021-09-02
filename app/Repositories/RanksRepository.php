<?php

namespace App\Repositories;

use App\Collections\RanksCollection;
use App\Filters\RanksFilter;
use App\Models\Rank;
use Illuminate\Database\Query\Expression;

class RanksRepository
{
    /**
     * Выборка действующих актуальных разрядов с фильтром по типу разряда
     */
    public function getRanksList(RanksFilter $filter): RanksCollection
    {
        $query = Rank::join('events', 'events.id', '=', 'ranks.event_id')->orderByRaw(new Expression('ranks.finish_date DESC, events.date DESC'));

        if ($filter->personId !== null) {
            $query->where('person_id', $filter->personId);
        }
        if ($filter->rank !== null) {
            $query->where('rank', $filter->rank);
        }
        if ($filter->with !== null) {
            $query->with($filter->with);
        }
        return new RanksCollection($query->get());
    }

    public function getLatestRank(int $personId): ?Rank
    {
        return Rank::where('person_id', '=', $personId)
            ->orderBy('finish_date', 'desc')
            ->limit(1)
            ->get()
            ->first();
    }

    public function storeRank(Rank $rank): Rank
    {
        $rank->save();
        return $rank;
    }
}
