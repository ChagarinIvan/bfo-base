<?php

declare(strict_types=1);

namespace App\Application\Service\Distance;

use App\Application\Dto\Distance\DistanceAssembler;
use App\Application\Dto\Distance\ViewDistanceDto;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Distance\DistanceRepository;
use App\Domain\Event\EventRepository;
use App\Domain\Event\EventResources;

final readonly class ListEventDistancesService
{
    public function __construct(
        private DistanceRepository $distances,
        private DistanceAssembler $assembler,
        private EventRepository $events,
    ) {
    }

    /** @return list<ViewDistanceDto> */
    public function execute(ListEventDistances $command): array
    {
        if ($command->isGuest() && $this->events->byId($command->eventId(), new EventResources(readyOnly: true)) === null) {
            throw new EventNotFound;
        }

        return $this->distances
            ->byCriteria($command->criteria())
            ->map($this->assembler->toViewDistanceDto(...))
            ->all()
        ;
    }
}
