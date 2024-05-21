<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Domain\Event\EventRepository;
use function array_map;

final readonly class ListEventsService
{
    public function __construct(
        private EventRepository $events,
        private EventAssembler $assembler,
    ) {
    }

    /** @return ViewEventDto[] */
    public function execute(ListEvents $command): array
    {
        $collection = $this->events->byCriteria($command->criteria());
        return array_map(
            $this->assembler->toViewEventDto(...),
            $collection->all(),
        );
    }
}
