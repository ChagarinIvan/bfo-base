<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Service\Club\ListLegacyClubsService;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Event\ViewEvent;
use App\Application\Service\Event\ViewEventService;
use App\Application\Service\Person\ListPersonsService;
use App\Domain\Club\ClubNameNormalizer;
use App\Domain\Distance\Distance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowEventDistanceAction extends BaseController
{
    use EventAction;
    use RendersEventDistance;

    /**
     * @url /events/d/{distance}
     */
    public function __invoke(
        Distance $distance,
        ViewEventService $eventService,
        ListPersonsService $personsService,
        ListLegacyClubsService $clubsService,
        ClubNameNormalizer $clubNameNormalizer,
    ): RedirectResponse|View {
        try {
            $event = $eventService->execute(new ViewEvent((string) $distance->event_id));
        } catch (EventNotFound) {
            return $this->redirectTo404Error();
        }

        return $this->renderEventDistance($event, $distance, $clubsService, $personsService, $clubNameNormalizer);
    }
}
