<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Event\Exception\InvalidProtocol;
use App\Domain\Auth\Impression;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Exception\InvalidProtocolContent;
use App\Domain\Event\Protocol\ProtocolFactory;
use App\Domain\Event\Protocol\ProtocolSource;
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
        private ProtocolFactory $protocolFactory,
    ) {
    }

    /** @throws EventNotFound */
    public function execute(UpdateEvent $command): ViewEventDto
    {
        return $this->transactional->run(function () use ($command): ViewEventDto {
            $event = $this->events->lockById($command->id()) ?? throw new EventNotFound;
            $impression = new Impression($this->clock->now(), $command->userId());

            $event->updateInfo($command->input(), $impression);

            $protocolSource = $command->protocolSource();
            if ($protocolSource instanceof ProtocolSource) {
                try {
                    $protocol = $this->protocolFactory->create($protocolSource);
                } catch (InvalidProtocolContent $exception) {
                    throw new InvalidProtocol($exception);
                }

                $event->updateProtocol($this->protocolUpdater, $protocol, $impression);
            }

            $this->events->update($event);

            return $this->assembler->toViewEventDto($event);
        });
    }
}
