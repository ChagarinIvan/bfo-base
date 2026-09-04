<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListEventsService
{
    public function __construct(
        private EventRepository $events,
        private EventAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewEventDto> */
    public function execute(ListEvents $command): Slice
    {
        return $this->events
            ->paginate($command->criteria(), $command->resources())
            ->map($this->assembler->toViewEventDto(...))
        ;
    }
}
