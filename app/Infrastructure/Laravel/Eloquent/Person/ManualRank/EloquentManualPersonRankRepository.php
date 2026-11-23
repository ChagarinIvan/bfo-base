<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person\ManualRank;

use App\Domain\Person\ManualRank\ManualPersonRank;
use App\Domain\Person\ManualRank\ManualPersonRankRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Database\Eloquent\Builder;

final class EloquentManualPersonRankRepository implements ManualPersonRankRepository
{
    public function byCriteria(Criteria $criteria): array
    {
        return $this->query($criteria)->orderBy('achieved_on')->orderBy('id')->get()->all();
    }

    public function oneByCriteria(Criteria $criteria): ?ManualPersonRank
    {
        return $this->query($criteria)->first();
    }

    public function add(ManualPersonRank $rank): void
    {
        $rank->create();
    }

    public function update(ManualPersonRank $rank): void
    {
        $rank->save();
    }

    /** @return Builder<ManualPersonRank> */
    private function query(Criteria $criteria): Builder
    {
        $query = ManualPersonRank::query()->where('active', true);
        foreach (['id' => 'id', 'personId' => 'person_id', 'rank' => 'rank', 'achievedOn' => 'achieved_on', 'activatedOn' => 'activated_on'] as $key => $column) {
            if ($criteria->hasParam($key)) {
                $query->where($column, $criteria->param($key));
            }
        }

        return $query;
    }
}
