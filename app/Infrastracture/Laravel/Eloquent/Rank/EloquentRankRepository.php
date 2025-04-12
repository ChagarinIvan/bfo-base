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

    public function deleteByCriteria(Criteria $criteria): void
    {
        $this->buildQuery($criteria)->delete();
    }

    private function buildQuery(Criteria $criteria): Builder
    {
        $query = Rank::select('ranks.*');

        if ($criteria->hasParam('person_id') || $criteria->hasParam('personId')) {
            $query->where('person_id', $criteria->paramOrDefault('person_id') ?? $criteria->param('personId'));
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

        $query->join('events', 'events.id', '=', 'ranks.event_id');
        if ($criteria->sorting()) {
            foreach ($criteria->sorting() as $key => $order) {
                $query->orderBy($key, $order);
            }
        } else {
            $query->orderBy('finish_date', 'desc')->orderBy('events.date', 'desc');
        }

        return $query;
    }
}
