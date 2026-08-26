<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\CupEvent;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

final class EloquentCupEventRepository implements CupEventRepository
{
    public function byId(int $id): ?CupEvent
    {
        return CupEvent::where('active', true)->find($id);
    }

    public function lockById(int $id): ?CupEvent
    {
        return CupEvent::where('active', true)->lockForUpdate()->find($id);
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        $query = CupEvent::where('active', true)
            ->with(['event.competition', 'event.protocolLines', 'event.distances', 'event.cups', 'event.flags'])
            ->orderByDesc('id')
        ;

        if ($criteria->hasParam('cupId')) {
            $query->where('cup_id', $criteria->param('cupId'));
        }

        return $query->get();
    }

    public function update(CupEvent $cupEvent): void
    {
        $cupEvent->save();
    }
}
