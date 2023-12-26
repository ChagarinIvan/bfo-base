<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditCupEventFormAction extends AbstractCupAction
{
    public function __invoke(string $cupId, int $cupEventId): View|RedirectResponse
    {
        $cup = $this->cupsService->getCup((int) $cupId);
        $cupEvent = $this->cupEventsService->getCupEvent($cupEventId);

        $events = $this->eventsRepository->getYearEvents($cup->year);

        return $this->view('cup.events.edit', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'events' => $events,
        ]);
    }
}
