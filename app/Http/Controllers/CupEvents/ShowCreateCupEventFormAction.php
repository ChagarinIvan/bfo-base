<?php

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use App\Models\Cup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCreateCupEventFormAction extends AbstractCupAction
{
    public function __invoke(Cup $cup): View|RedirectResponse
    {
        $events = $this->eventsRepository->getYearEvents($cup->year);
        $events = $events->whereNotIn('id', $cup->events->pluck('event_id'))
            ->sortBy('date');

        return $this->view('cup.events.create', [
            'cup' => $cup,
            'events' => $events,
        ]);
    }
}
