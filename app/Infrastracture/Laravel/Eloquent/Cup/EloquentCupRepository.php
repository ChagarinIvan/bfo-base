<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Cup;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

final class EloquentCupRepository implements CupRepository
{
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
        $query = Cup::where('active', true)->orderByDesc('id');

        if ($criteria->hasParam('visible')) {
            $query->where('visible', $criteria->param('visible'));
        }

        if ($criteria->hasParam('year')) {
            $query->where('year', $criteria->param('year'));
        }

        return $query->get();
    }

    public function update(Cup $cup): void
    {
        $cup->save();
    }
}
