<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;

final readonly class AllEventsService
{
    public function __construct(
        private EventRepository $events,
        private EventAssembler $assembler,
    ) {
    }

    /** @return list<ViewEventDto> */
    public function execute(AllEvents $command): array
    {
        $resources = $command->resources();

        return $this->events
            ->byCriteria($command->criteria(), $resources)
            ->map(fn (Event $event): ViewEventDto => $this->assembler->toViewEventDto($event, $resources))
            ->all()
        ;
    }
}
