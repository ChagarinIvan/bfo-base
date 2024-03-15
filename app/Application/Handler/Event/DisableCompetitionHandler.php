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
use App\Services\EventService;

final readonly class DisableCompetitionHandler
{
    public function __construct(
        private ListEventsService $service,
        private DisableEventService $eventService,
    ) {
    }

    public function handle(CompetitionDisabled $event): void
    {
        foreach ($this->service->execute(new ListEvents(new EventSearchDto((string) $event->competition->id))) as $eventDto) {
            $this->eventService->execute(new DisableEvent($eventDto->id, new UserId($event->competition->updated->by)));
        }
    }
}
