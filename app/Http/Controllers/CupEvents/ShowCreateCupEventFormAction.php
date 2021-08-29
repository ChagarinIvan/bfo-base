<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupViewAction;
use App\Models\Cup;
use App\Models\Event;
use Illuminate\Contracts\View\View;

class ShowCreateCupEventFormAction extends AbstractCupViewAction
{
    public function __invoke(Cup $cup): View
    {
        $events = Event::with('competition')
            ->where('date', 'LIKE', "%{$cup->year}%")
            ->whereNotIn('id', $cup->events->pluck('event_id'))
            ->orderBy('date')
            ->get();

        return $this->view('cup.events.create', [
            'cup' => $cup,
            'events' => $events,
        ]);
    }
}
