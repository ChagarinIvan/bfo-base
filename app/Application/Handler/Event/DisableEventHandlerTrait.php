<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Cup\ClearCupCache;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Domain\Event\Event;

trait DisableEventHandlerTrait
{
    protected function cleanUp(Event $event): void
    {
        $personIds = $this->protocolLineService->personIdsForEvent($event);
        $this->distanceService->deleteEventDistances($event);
        $this->protocolLineService->deleteEventLines($event);

        foreach ($personIds as $personId) {
            $this->rebuildPersonRanksService->execute(new RebuildPersonRanks($personId, new UserId($event->updated->by)));
        }

        foreach ($event->cups as $cup) {
            $this->clearCupCacheService->execute(new ClearCupCache((string) $cup->id));
        }
    }
}
