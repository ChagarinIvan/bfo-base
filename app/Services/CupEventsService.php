<?php

namespace App\Services;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\Group;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Support\Collection;

class CupEventsService
{
    public function __construct(private CacheManager $cache)
    {}

    public function getCupEvents(Cup $cup): Collection
    {
        return $cup->events()->with('event')->get();
    }

    public function getCupEventPersonsCount(CupEvent $cupEvent): int
    {
        $cupType = $cupEvent->cup->getCupType();
        return $cupType->getCupEventParticipatesCount($cupEvent);
    }

    public function getCupEvent(int $cupEventId): CupEvent
    {
        $cupEvent = CupEvent::find($cupEventId);
        if ($cupEvent) {
            return $cupEvent;
        }
        throw new \RuntimeException('Wrong cup event id.');
    }

    public function storeCupEvent(CupEvent $cupEvent): void
    {
        $cupEvent->save();
    }

    public function calculateCup(Cup $cup, Collection $cupEvents, Group $group): array
    {
        return $this->cache->tags(['cups', $cup->id])->remember(
            "{$cup->id}_{$group->id}",
            1000000,
            function () use ($cup, $cupEvents, $group) {
                $cupType = $cup->getCupType();
                return $cupType->calculateCup($cup, $cupEvents, $group);
            }
        );
    }
}
