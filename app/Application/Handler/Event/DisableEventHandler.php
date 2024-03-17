<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Event\Event;
use App\Domain\Event\Event\EventDisabled;
use App\Services\DistanceService;
use App\Services\ProtocolLineService;
use App\Services\RankService;

final readonly class DisableEventHandler
{
    public function __construct(
        // TODO replace old services with new
        private RankService $ranksService,
        private ProtocolLineService $protocolLineService,
        private DistanceService $distanceService,
    ) {
    }

    public function handle(EventDisabled $event): void
    {
        $this->distanceService->deleteEventDistances($event->event);
        $this->protocolLineService->deleteEventLines($event->event);
        $this->ranksService->deleteEventRanks($event->event);
    }
}
