<?php

namespace App\Repositories;

use App\Collections\RanksCollection;
use App\Filters\RanksFilter;
use App\Models\Rank;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;

class RanksRepository
{
    private const TABLE = 'ranks';

    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Выборка действующих актуальных разрядов с фильтром по типу разряда
     */
    public function getRanksList(RanksFilter $filter): RanksCollection
    {
        $ranks = Rank::selectRaw(new Expression('ranks.*'))
            ->leftJoin('events', 'events.id', '=', 'ranks.event_id');

        if ($filter->isOrderDescByFinishDate) {
            $ranks->orderByRaw(new Expression('ranks.finish_date DESC, events.date DESC'));
        } elseif ($filter->isOrderByFinish) {
            $ranks->orderByRaw(new Expression('ranks.finish_date'));
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

        return new RanksCollection($ranks->get());
    }

    public function getDateRank(int $personId, Carbon $date = null): ?Rank
    {
        $rankQuery = Rank::where('person_id', '=', $personId)
            ->orderBy('finish_date', 'desc')
            ->limit(1);

        if ($date !== null) {
            $rankQuery->where('finish_date', '>=', $date);
            $rankQuery->where('start_date', '<=', $date);
        }
        return $rankQuery->get()->first();
    }

    public function storeRank(Rank $rank): Rank
    {
        $rank->save();
        return $rank;
    }

    public function cleanAll(): void
    {
        $this->db->table(self::TABLE)->truncate();
    }

    public function deleteRanks(RanksCollection $ranks): void
    {
        $ranks->each(fn(Rank $rank) => $rank->delete());
    }
}
