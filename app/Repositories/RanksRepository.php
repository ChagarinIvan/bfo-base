<?php

namespace App\Repositories;

use App\Collections\RanksCollection;
use App\Filters\RanksFilter;
use App\Models\Rank;
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
            ->join('events', 'events.id', '=', 'ranks.event_id');

        if ($filter->isOrderDescByFinishDateAnd) {
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
        if ($filter->date !== null) {
            $ranks->where('finish_date', '>=', $filter->date);
            $ranks->where('start_date', '<=', $filter->date);
        }
        if ($filter->with !== null) {
            $ranks->with($filter->with);
        }
        return new RanksCollection($ranks->get());
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

    public function cleanAll(): void
    {
        $this->db->table(self::TABLE)->truncate();
    }
}
