<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\LegacyViewEventDto;
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
    public function execute(ViewEvent $command): LegacyViewEventDto
    {
        $event = $this->events->byId($command->id()) ?? throw new EventNotFound();

        return $this->assembler->toLegacyViewEventDto($event);
    }
}
