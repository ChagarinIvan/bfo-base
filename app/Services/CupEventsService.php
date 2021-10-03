<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\Group;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;

class CupEventsService
{
    private CacheManager $cache;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    public function getCupEvents(Cup $cup): Collection
    {
        return $cup->events()->with('event')->get();
    }

    public function getCupEventPersonsCount(CupEvent $cupEvent): int
    {
        $cupType = $cupEvent->cup->getCupType();
        return $cupType->getCupEventParticipatesCount($cupEvent);
    }

    public function getCupEvent(int $cupEventId): ?CupEvent
    {
        return CupEvent::find($cupEventId);
    }

    public function storeCupEvent(CupEvent $cupEvent): void
    {
        $cupEvent->save();
    }

    public function calculateCup(Cup $cup, Collection $cupEvents, Group $group): array
    {
        return $this->cache->store('redis')->remember(
            "{$cup->id}_{$group->id}",
            1000000,
            function () use ($cup, $cupEvents, $group) {
                $cupType = $cup->getCupType();
                return $cupType->calculateCup($cup, $cupEvents, $group);
            }
        );
    }
}
