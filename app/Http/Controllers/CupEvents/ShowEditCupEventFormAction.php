<?php

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditCupEventFormAction extends AbstractCupAction
{
    public function __invoke(int $cupId, int $cupEventId): View|RedirectResponse
    {
        $cup = $this->cupsRepository->getCup($cupId);
        $cupEvent = $this->cupEventsService->getCupEvent($cupEventId);

        if ($cup === null || $cupEvent === null) {
            return $this->redirectToError();
        }

        $events = $this->eventsRepository->getYearEvents($cup->year);

        return $this->view('cup.events.edit', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'events' => $events,
        ]);
    }
}
