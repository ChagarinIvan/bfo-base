<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Club;

use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use function mb_strtolower;

final class EloquentClubRepository implements ClubRepository
{
    public function add(Club $club): void
    {
        $club->create();
    }

    public function byId(int $id): ?Club
    {
        return Club::where('active', true)
            ->withCount(['persons' => static fn (Builder $persons): Builder => $persons->where('active', true)])
            ->find($id)
        ;
    }

    public function lockById(int $id): ?Club
    {
        return Club::where('active', true)
            ->withCount(['persons' => static fn (Builder $persons): Builder => $persons->where('active', true)])
            ->lockForUpdate()
            ->find($id)
        ;
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

    /** @return Slice<Club> */
    public function paginate(Criteria $criteria): Slice
    {
        return new Slice(new EloquentQueryAdapter($this->createPaginatedQuery($criteria)));
    }

    /** @return Builder<Club> */
    private function createPaginatedQuery(Criteria $criteria): Builder
    {
        $query = Club::query()
            ->where('active', true)
            ->withCount(['persons' => static fn (Builder $persons): Builder => $persons->where('active', true)])
            ->orderBy('name')
            ->orderBy('id');

        if ($criteria->hasParam('name')) {
            $query->whereRaw(
                'LOWER(name) LIKE ?',
                ['%' . mb_strtolower((string) $criteria->param('name')) . '%'],
            );
        }

        return $query;
    }

    private function buildQuery(Criteria $criteria): Builder
    {
        $query = Club::where('active', true)->orderBy('name');

        if ($criteria->hasParam('withPersonsCount')) {
            $query->withCount('persons');
        }

        if ($criteria->hasParam('ids')) {
            $query->whereIn('id', $criteria->param('ids'));
        }

        if ($criteria->hasParam('name')) {
            $query->where('name', $criteria->param('name'));
        }

        if ($criteria->hasParam('normalizedName')) {
            $query->where('normalize_name', $criteria->param('normalizedName'));
        }

        if ($criteria->hasParam('excludeId')) {
            $query->where('id', '!=', $criteria->param('excludeId'));
        }

        return $query;
    }
}
