<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Rank;

use App\Domain\Rank\Rank;
use App\Domain\Rank\RankRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentRankRepository implements RankRepository
{
    public function add(Rank $rank): void
    {
        $rank->create();
    }

    public function byId(int $id): ?Rank
    {
        return Rank::find($id);
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->buildQuery($criteria)->get();
    }

    public function oneByCriteria(Criteria $criteria): ?Rank
    {
        /** @var null|Rank $rank */
        $rank = $this->buildQuery($criteria)->first();

        return $rank;
    }

    private function buildQuery(Criteria $criteria): Builder
    {
        $query = Rank::select('*');

        if ($criteria->hasParam('person_id')) {
            $query->where('person_id', $criteria->param('person_id'));
        }

        if ($date = $criteria->paramOrDefault('date')) {
            $query->where('finish_date', '>=', $date);
            $query->where('start_date', '<=', $date);
        }

        if ($criteria->hasParam('finish_date_to')) {
            $query->where('finish_date', '<=', $criteria->param('finish_date_to'));
        }

        if ($criteria->hasParam('startDateLess')) {
            $query->where('start_date', '<=', $criteria->param('startDateLess'));
        }

        if ($criteria->hasParam('activated')) {
            if ($criteria->param('activated')) {
                $query->whereNotNull('activated_date');
            } else {
                $query->whereNull('activated_date');
            }
        }

        if ($criteria->sorting()) {
            $query->join('events', 'events.id', '=', 'ranks.event_id');
            foreach ($criteria->sorting() as $key => $order) {
                $query->orderBy($key, $order);
            }
        } else {
            $query->orderBy('finish_date', 'desc');
        }

        return $query;
    }
}
