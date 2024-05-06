<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Application\Dto\Event\EventSearchDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\ViewCup;
use App\Application\Service\Cup\ViewCupService;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use App\Bridge\Laravel\Http\Controllers\Cup\CupAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowCreateCupEventFormAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        ViewCupService $viewCupService,
        ListEventsService $listEvents,
    ): View|RedirectResponse {
        try {
            $cup = $viewCupService->execute(new ViewCup($cupId));
        } catch (CupNotFound) {
            return $this->redirectTo404Error();
        }

        $events = $listEvents->execute(new ListEvents(new EventSearchDto(
            year: (string) $cup->year,
            idNotIn: array_column($cup->cupEvents, 'eventId'),
        )));

        /** @see /resources/views/cup/events/create.blade.php */
        return $this->view('cup.events.create', [
            'cup' => $cup,
            'events' => $events,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
