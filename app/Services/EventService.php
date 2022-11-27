<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Cache\Repository as CacheManager;

class EventService
{
    public function __construct(
        private readonly private RankService $ranksService,
        private readonly private ProtocolLineService $protocolLineService,
        private readonly private DistanceService $distanceService,
        private readonly private CacheManager $cache
    ) {}

    public function deleteEvent(Event $event): void
    {
        $this->distanceService->deleteEventDistances($event);
        $this->protocolLineService->deleteEventLines($event);
        $this->ranksService->deleteEventRanks($event);
        $event->delete();
    }

    public function storeEvent(Event $event): void
    {
        $event->save();
        foreach ($event->cups as $cup) {
            $this->cache->tags(['cups', $cup->id])->flush();
        }
    }
}
