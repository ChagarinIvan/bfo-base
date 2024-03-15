<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class DisableEventService
{
    public function __construct(
        private EventRepository $events,
        private Clock $clock,
        private TransactionManager $transactional,
    ) {
    }

    /** @throws EventNotFound */
    public function execute(DisableEvent $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $event = $this->events->lockById($command->id()) ?? throw new EventNotFound();
            $impression = new Impression($this->clock->now(), $command->userId());
            $event->disable($impression);

            $this->events->update($event);
        });
    }
}
