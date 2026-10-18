<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Event\EventRepository;
use App\Domain\Event\EventResultsCleaner;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class CleanupEventResultsService
{
    public function __construct(
        private EventRepository $events,
        private EventResultsCleaner $cleaner,
        private TransactionManager $transactional,
        private Clock $clock,
    ) {
    }

    public function execute(CleanupEventResults $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $event = $this->events->lockById($command->eventId, includeInactive: true) ?? throw new EventNotFound();

            $event->cleanResults(
                $command->processingToken,
                $command->startParsing,
                $this->cleaner,
                new Impression($this->clock->now(), $event->updated->by),
            );

            $this->events->update($event);
        });
    }
}
