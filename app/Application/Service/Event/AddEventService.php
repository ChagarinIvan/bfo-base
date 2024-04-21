<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\ProtocolStorage;

final readonly class AddEventService
{
    public function __construct(
        private EventFactory $factory,
        private EventRepository $events,
        private EventAssembler $assembler,
    ) {
    }

    public function execute(AddEvent $command): ViewEventDto
    {
        $event = $this->factory->create($command->eventInput());
        $this->events->add($event);

        return $this->assembler->toViewEventDto($event);
    }
}
