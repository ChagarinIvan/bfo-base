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
        private ListEventsService $listEvents,
        private DisableEventService $disableEventService,
    ) {
    }

    public function handle(CompetitionDisabled $systemEvent): void
    {
        $events = $this->listEvents->execute(
            new ListEvents(new EventSearchDto(competitionId: (string) $systemEvent->competition->id))
        );

        foreach ($events as $eventDto) {
            $this->disableEventService->execute(
                new DisableEvent($eventDto->id, new UserId($systemEvent->competition->updated->by))
            );
        }
    }
}
