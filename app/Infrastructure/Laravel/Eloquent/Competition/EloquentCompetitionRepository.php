<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Competition;

use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Database\Eloquent\Builder;
use function mb_strtolower;

final class EloquentCompetitionRepository implements CompetitionRepository
{
    public function add(Competition $competition): void
    {
        $competition->create();
    }

    public function update(Competition $competition): void
    {
        $competition->save();
    }

    public function byId(int $id): ?Competition
    {
        return Competition::where('active', true)->find($id);
    }

    /** @return Slice<Competition> */
    public function paginate(Criteria $criteria): Slice
    {
        return new Slice(new EloquentQueryAdapter($this->createQuery($criteria)));
    }

    public function lockById(int $id): ?Competition
    {
        return Competition::where('active', true)->lockForUpdate()->find($id);
    }

    /** @return Builder<Competition> */
    private function createQuery(Criteria $criteria): Builder
    {
        $query = Competition::where('active', true)->orderByDesc('from');

        if ($criteria->hasParam('year')) {
            $query
                ->where('from', '>=', "{$criteria->param('year')}-01-01")
                ->where('to', '<=', "{$criteria->param('year')}-12-31")
            ;
        }

        if ($criteria->hasParam('name')) {
            $query->whereRaw(
                'LOWER(name) LIKE ?',
                ['%' . mb_strtolower((string) $criteria->param('name')) . '%'],
            );
        }

        if ($criteria->hasParam('date')) {
            $query
                ->where('from', '<=', $criteria->param('date'))
                ->where('to', '>=', $criteria->param('date'))
            ;
        }

        return $query;
    }
}
