<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Event\Event;
use App\Domain\Event\Event\EventDisabled;
use App\Services\CupsService;
use App\Services\DistanceService;
use App\Services\ProtocolLineService;
use App\Services\RankService;

trait DisableEventHandlerTrait
{
    protected function cleanUp(Event $event): void
    {
        dump('Cleaning up event '.$event->name);
        $this->distanceService->deleteEventDistances($event);
        $this->protocolLineService->deleteEventLines($event);
        $this->ranksService->deleteEventRanks($event);

        foreach ($event->cups as $cup) {
            $this->cupsService->clearCupCache($cup->id);
        }
    }
}
