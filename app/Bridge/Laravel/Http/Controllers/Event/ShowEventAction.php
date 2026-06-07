<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Service\Club\ListClubsService;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Event\ViewEvent;
use App\Application\Service\Event\ViewEventService;
use App\Application\Service\Person\ListPersonsService;
use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowEventAction extends BaseController
{
    use EventAction;
    use RendersEventDistance;

    /**
     * @url /events/{eventId}
     */
    public function __invoke(
        string $eventId,
        ViewEventService $eventService,
        ListPersonsService $personsService,
        ListClubsService $clubsService,
    ): View|RedirectResponse {
        try {
            $event = $eventService->execute(new ViewEvent($eventId));
        } catch (EventNotFound) {
            return $this->redirectTo404Error();
        }

        $distance = $event->firstDistance;
        if ($distance === null) {
            return $this->redirector->action(ShowCompetitionAction::class, [$event->competitionId]);
        }

        return $this->renderEventDistance($event, $distance, $clubsService, $personsService);
    }
}
