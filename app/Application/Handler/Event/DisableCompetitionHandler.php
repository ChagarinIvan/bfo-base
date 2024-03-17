<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventSearchDto;
use App\Application\Service\Event\DisableEvent;
use App\Application\Service\Event\DisableEventService;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use App\Domain\Competition\Event\CompetitionDisabled;

final readonly class DisableCompetitionHandler
{
    public function __construct(
        private ListEventsService $service,
        private DisableEventService $eventService,
    ) {
    }

    public function handle(CompetitionDisabled $event): void
    {
        $events = $this->service->execute(
            new ListEvents(new EventSearchDto(competitionId: (string) $event->competition->id))
        );

        foreach ($events as $eventDto) {
            $this->eventService->execute(
                new DisableEvent($eventDto->id, new UserId((int) $event->competition->updated->by))
            );
        }
    }
}
