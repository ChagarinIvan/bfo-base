<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventProtocolDto;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Event\EventRepository;
use App\Domain\Event\ProtocolStorage;

final readonly class DownloadEventProtocolService
{
    public function __construct(
        private EventRepository $events,
        private ProtocolStorage $storage,
        private EventAssembler $assembler,
    ) {
    }

    /** @throws EventNotFound */
    public function execute(DownloadEventProtocol $command): ViewEventProtocolDto
    {
        $event = $this->events->byId($command->id()) ?? throw new EventNotFound();
        $protocol = $this->storage->get($event->file);

        return $this->assembler->toViewEventProtocolDto($event, $protocol);
    }
}
