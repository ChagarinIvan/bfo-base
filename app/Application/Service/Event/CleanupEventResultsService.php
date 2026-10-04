<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Auth\Impression;
use App\Domain\Cup\CupCacheInvalidator;
use App\Domain\Distance\DistanceDeleter;
use App\Domain\Event\EventRepository;
use App\Domain\ProtocolLine\ProtocolLineOperations;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class CleanupEventResultsService
{
    public function __construct(
        private EventRepository $events,
        private ProtocolLineOperations $protocolLines,
        private DistanceDeleter $distances,
        private RebuildPersonRanksService $rebuildPersonRanks,
        private CupCacheInvalidator $invalidator,
        private TransactionManager $transactional,
        private Clock $clock,
    ) {
    }

    public function execute(CleanupEventResults $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $event = $this->events->lockById($command->eventId, includeInactive: true) ?? throw new EventNotFound();

            if ($event->processing_token !== $command->processingToken) {
                return;
            }

            if ($command->startParsing && !$event->isParsing($command->processingToken)) {
                return;
            }

            $personIds = $this->protocolLines->personIdsForEvent($event);
            $this->distances->deleteForEvent($event->id);
            $this->protocolLines->deleteEventLines($event);

            foreach ($personIds as $personId) {
                try {
                    $this->rebuildPersonRanks->execute(new RebuildPersonRanks($personId, new UserId($event->updated->by)));
                } catch (PersonNotFound) {
                    // A former participant may have been disabled since the protocol was processed.
                }
            }

            $event->protocolResultsCleaned(
                $command->processingToken,
                new Impression($this->clock->now(), $event->updated->by),
                $this->invalidator,
            );

            $this->events->update($event);
        });
    }
}
