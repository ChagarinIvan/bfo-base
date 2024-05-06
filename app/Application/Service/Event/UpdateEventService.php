<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Event\EventRepository;
use App\Domain\Event\ProtocolUpdater;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class UpdateEventService
{
    public function __construct(
        private Clock $clock,
        private ProtocolUpdater $protocolUpdater,
        private EventRepository $events,
        private EventAssembler $assembler,
        private TransactionManager $transactional,
    ) {
    }

    /** @throws EventNotFound */
    public function execute(UpdateEvent $command): ViewEventDto
    {
        return $this->transactional->run(function () use ($command): ViewEventDto {
            $event = $this->events->lockById($command->id()) ?? throw new EventNotFound;
            $impression = new Impression($this->clock->now(), $command->userId());
            $event->updateData($this->protocolUpdater, $command->input(), $impression);
            $this->events->update($event);

            return $this->assembler->toViewEventDto($event);
        });
    }
}
