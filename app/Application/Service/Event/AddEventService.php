<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Event\Exception\InvalidProtocol;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Exception\InvalidProtocolContent;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Protocol\ProtocolFactory;

final readonly class AddEventService
{
    public function __construct(
        private EventFactory $factory,
        private EventRepository $events,
        private EventAssembler $assembler,
        private ProtocolFactory $protocolFactory,
    ) {
    }

    public function execute(AddEvent $command): ViewEventDto
    {
        try {
            $protocol = $this->protocolFactory->create($command->protocolSource());
        } catch (InvalidProtocolContent $exception) {
            throw new InvalidProtocol($exception);
        }

        $event = $this->factory->create($command->eventInput(), $protocol);
        $this->events->add($event);

        return $this->assembler->toViewEventDto($event);
    }
}
