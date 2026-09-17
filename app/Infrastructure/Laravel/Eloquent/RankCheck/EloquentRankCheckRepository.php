<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\RankCheck;

use App\Domain\RankCheck\RankCheck;
use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Support\Collection;

final readonly class EloquentRankCheckRepository implements RankCheckRepository
{
    public function byId(int $id): ?RankCheck
    {
        return RankCheck::query()->find($id);
    }

    /** @return Slice<RankCheck> */
    public function paginate(Criteria $criteria): Slice
    {
        return new Slice(new EloquentQueryAdapter(
            RankCheck::query()->orderByDesc('created_at')->orderByDesc('id'),
        ));
    }

    public function lockById(int $id): ?RankCheck
    {
        return RankCheck::query()->lockForUpdate()->find($id);
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        $query = RankCheck::query();

        if ($criteria->hasParam('createdBefore')) {
            $query->where('created_at', '<', $criteria->param('createdBefore'));
        }

        return $query->get();
    }

    public function add(RankCheck $check): void
    {
        $check->create();
    }

    public function delete(RankCheck $check): void
    {
        RankCheck::query()->whereKey($check->id)->delete();
    }

    public function update(RankCheck $check): void
    {
        $check->save();
    }
}
