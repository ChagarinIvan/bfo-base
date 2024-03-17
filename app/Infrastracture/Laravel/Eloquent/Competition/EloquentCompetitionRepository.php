<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Competition;

use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

final class EloquentCompetitionRepository implements CompetitionRepository
{
    public function add(Competition $competition): void
    {
        $competition->save();
    }

    public function update(Competition $competition): void
    {
        $competition->save();
    }

    public function byId(int $id): ?Competition
    {
        return Competition::where('active', true)->find($id);
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        $query = Competition::where('active', true)->orderByDesc('from');

        if ($criteria->hasParam('year')) {
            $query
                ->where('from', '>=', "{$criteria->param('year')}-01-01")
                ->where('to', '<=', "{$criteria->param('year')}-12-31")
            ;
        }

        return $query->get();
    }

    public function lockById(int $id): ?Competition
    {
        return Competition::where('active', true)->lockForUpdate()->find($id);
    }
}
