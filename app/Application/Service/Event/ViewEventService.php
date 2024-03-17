<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Event\EventRepository;

final readonly class ViewEventService
{
    public function __construct(
        private EventRepository $events,
        private EventAssembler $assembler,
    ) {
    }

    /** @throws EventNotFound */
    public function execute(ViewEvent $command): ViewEventDto
    {
        $event = $this->events->byId($command->id()) ?? throw new EventNotFound();

        return $this->assembler->toViewEventDto($event);
    }
}
