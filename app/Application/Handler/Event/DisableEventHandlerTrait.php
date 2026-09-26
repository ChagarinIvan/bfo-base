<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Domain\Event\Event;

trait DisableEventHandlerTrait
{
    protected function cleanUp(Event $event): void
    {
        $personIds = $this->protocolLineService->personIdsForEvent($event);
        $this->distanceDeleter->deleteForEvent($event->id);
        $this->protocolLineService->deleteEventLines($event);

        foreach ($personIds as $personId) {
            try {
                $this->rebuildPersonRanksService->execute(new RebuildPersonRanks($personId, new UserId($event->updated->by)));
            } catch (PersonNotFound) {
                // A queued protocol update may outlive a deleted participant.
            }
        }

        if ($event->cups->isNotEmpty()) {
            $this->clearCupCacheService->execute();
        }
    }
}
