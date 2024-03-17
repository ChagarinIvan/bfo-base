<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Club;

use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentClubRepository implements ClubRepository
{
    public function add(Club $club): void
    {
        $club->save();
    }

    public function byId(int $id): ?Club
    {
        return Club::where('active', true)->find($id);
    }

    public function lockById(int $id): ?Club
    {
        return Club::where('active', true)->lockForUpdate()->find($id);
    }

    public function update(Club $club): void
    {
        $club->save();
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->buildQuery($criteria)->get();
    }

    public function oneByCriteria(Criteria $criteria): ?Club
    {
        /** @var Club|null $club */
        $club = $this->buildQuery($criteria)->first();

        return $club;
    }

    private function buildQuery(Criteria $criteria): Builder
    {
        $query = Club::where('active', true)->orderBy('name');

        if ($criteria->hasParam('name')) {
            $query->where('name', $criteria->param('name'));
        }

        return $query;
    }
}
