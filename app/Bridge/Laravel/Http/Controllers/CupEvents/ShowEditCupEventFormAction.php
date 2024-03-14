<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Bridge\Laravel\Http\Controllers\Cups\AbstractCupAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditCupEventFormAction extends AbstractCupAction
{
    public function __invoke(string $cupId, string $cupEventId): View|RedirectResponse
    {
        $cup = $this->cupsService->getCup((int) $cupId);
        $cupEvent = $this->cupEventsService->getCupEvent((int) $cupEventId);

        $events = $this->eventsRepository->getYearEvents($cup->year);

        return $this->view('cup.events.edit', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'events' => $events,
        ]);
    }
}
