<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group\CupGroup;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Support\Collection;
use RuntimeException;

final readonly class CupEventsService
{
    public function __construct(private CacheManager $cache)
    {
    }

    public function getCupEvents(Cup $cup): Collection
    {
        return $cup->events()->with('event')->get();
    }

    public function getCupEventPersonsCount(CupEvent $cupEvent): int
    {
        return $cupEvent->cup
            ->getCupType()
            ->getCupEventParticipatesCount($cupEvent)
        ;
    }

    public function getCupEvent(int $cupEventId): CupEvent
    {
        return CupEvent::find($cupEventId) ?? throw new RuntimeException('Wrong cup event id.');
    }

    public function storeCupEvent(CupEvent $cupEvent): void
    {
        $cupEvent->save();
    }

    /** @return array<string, CupEventPoint[]> */
    public function calculateCup(Cup $cup, Collection $cupEvents, CupGroup $group): array
    {
        return $this->cache->tags(['cups', $cup->id])->remember(
            "{$cup->id}_{$group->id()}",
            1000000,
            static function () use ($cup, $cupEvents, $group) {
                return $cup->getCupType()->calculateCup($cup, $cupEvents, $group);
            }
        );
    }
}
