<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Cup;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use App\Infrastructure\Laravel\Eloquent\Shared\EscapesLikePatterns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use function mb_strtolower;

final class EloquentCupRepository implements CupRepository
{
    use EscapesLikePatterns;

    public function add(Cup $cup): void
    {
        $cup->create();
    }

    public function lockById(int $id): ?Cup
    {
        return Cup::where('active', true)->lockForUpdate()->find($id);
    }

    public function byId(int $id): ?Cup
    {
        return Cup::where('active', true)->find($id);
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->applyCriteria(
            Cup::where('active', true)->orderByDesc('id'),
            $criteria,
        )->get();
    }

    /** @return Slice<Cup> */
    public function paginate(Criteria $criteria): Slice
    {
        return new Slice(new EloquentQueryAdapter($this->createQuery($criteria)));
    }

    public function update(Cup $cup): void
    {
        $cup->save();
    }

    /** @return Builder<Cup> */
    private function createQuery(Criteria $criteria): Builder
    {
        $query = Cup::where('active', true)->orderByDesc('year')->orderByDesc('id');

        return $this->applyCriteria($query, $criteria);
    }

    /** @param Builder<Cup> $query
     * @return Builder<Cup>
     */
    private function applyCriteria(Builder $query, Criteria $criteria): Builder
    {
        if ($criteria->hasParam('visible')) {
            $query->where('visible', $criteria->param('visible'));
        }

        if ($criteria->hasParam('ids')) {
            $query->whereIn('id', $criteria->param('ids'));
        }

        if ($criteria->hasParam('year')) {
            $query->where('year', $criteria->param('year'));
        }

        if ($criteria->hasParam('name')) {
            $name = $this->escapeLikePattern(mb_strtolower((string) $criteria->param('name')));

            $query->whereRaw(
                "LOWER(name) LIKE ? ESCAPE '!'",
                ['%' . $name . '%'],
            );
        }

        return $query;
    }
}
