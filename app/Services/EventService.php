<?php

namespace App\Services;

use App\Models\Event;

class EventService
{
    private RankService $ranksService;
    private ProtocolLineService $protocolLineService;
    private DistanceService $distanceService;

    public function __construct(
        RankService $ranksService,
        ProtocolLineService $protocolLineService,
        DistanceService $distanceService
    ) {
        $this->ranksService = $ranksService;
        $this->protocolLineService = $protocolLineService;
        $this->distanceService = $distanceService;
    }

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
    }
}
