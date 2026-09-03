<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Service\Club\ListLegacyClubsService;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Event\ViewEvent;
use App\Application\Service\Event\ViewEventService;
use App\Application\Service\Person\ListLegacyPersonsService;
use App\Domain\Club\ClubNameNormalizer;
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
        ListLegacyPersonsService $personsService,
        ListLegacyClubsService $clubsService,
        ClubNameNormalizer $clubNameNormalizer,
    ): RedirectResponse|View {
        try {
            $event = $eventService->execute(new ViewEvent($eventId));
        } catch (EventNotFound) {
            return $this->redirectTo404Error();
        }

        $distance = $event->firstDistance;
        if ($distance === null) {
            return $this->redirector->to('/app/competitions/' . $event->competitionId);
        }

        return $this->renderEventDistance($event, $distance, $clubsService, $personsService, $clubNameNormalizer);
    }
}
