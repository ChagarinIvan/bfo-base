<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use App\Http\Controllers\Error\Show404ErrorAction;
use Illuminate\Contracts\View\View;

class ShowEditCupEventFormAction extends AbstractCupAction
{
    public function __invoke(int $cupId, int $cupEventId): View
    {
        $cup = $this->cupsRepository->getCup($cupId);
        $cupEvent = $this->cupEventsService->getCupEvent($cupEventId);

        if ($cup === null || $cupEvent === null) {
            $this->redirector->action(Show404ErrorAction::class);
        }

        $events = $this->eventsRepository->getYearEvents($cup->year);

        return $this->view('cup.events.edit', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'events' => $events,
        ]);
    }
}
