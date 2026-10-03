<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Event\EventRepository;
use App\Domain\ProtocolLine\ProtocolLineIdentifier;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class IdentifyProtocolLinesService
{
    public function __construct(
        private EventRepository $events,
        private ProtocolLineIdentifier $identifier,
        private TransactionManager $transactional,
        private Clock $clock,
    ) {
    }

    public function execute(IdentifyProtocolLines $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $event = $this->events->lockById($command->eventId) ?? throw new EventNotFound();

            $impression = new Impression($this->clock->now(), $command->impression->by);
            $event->ident($command->processingToken, $this->identifier, $impression);
            $this->events->update($event);
        });
    }
}
