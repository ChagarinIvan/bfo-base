<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Event\EventRepository;
use App\Domain\Event\EventResources;
use App\Domain\Event\Factory\UniteFactory;
use App\Domain\Event\UniteEventDataService;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;
use function count;

final readonly class UniteEventsService
{
    public function __construct(
        private EventRepository $events,
        private UniteFactory $factory,
        private UniteEventDataService $dataService,
        private Clock $clock,
        private EventAssembler $assembler,
        private TransactionManager $transactional,
    ) {
    }

    public function execute(UniteEvents $command): ViewEventDto
    {
        return $this->transactional->run(function () use ($command): ViewEventDto {
            $events = $this->events->lockByCriteria(
                $command->criteria(),
                new EventResources(withDistances: true, withProtocolLines: true),
            );

            if ($events->count() !== count($command->eventIds())) {
                throw new EventNotFound;
            }

            $event = $this->factory->create(
                $events,
                $command->competitionId(),
                new Impression($this->clock->now(), $command->userId()),
            );

            $this->events->add($event);
            $this->dataService->execute($events, $event);

            return $this->assembler->toViewEventDto($event);
        });
    }
}
