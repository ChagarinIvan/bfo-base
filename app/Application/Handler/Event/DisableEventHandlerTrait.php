<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Service\Cup\ClearCupCache;
use App\Domain\Event\Event;
use Illuminate\Support\Facades\Log;

trait DisableEventHandlerTrait
{
    protected function cleanUp(Event $event): void
    {
        Log::info('Cleaning up event ' . $event->name);

        $this->distanceService->deleteEventDistances($event);
        $this->protocolLineService->deleteEventLines($event);
        $this->ranksService->deleteEventRanks($event);

        foreach ($event->cups as $cup) {
            $this->clearCupCacheService->execute(new ClearCupCache((string) $cup->id));
        }
    }
}
