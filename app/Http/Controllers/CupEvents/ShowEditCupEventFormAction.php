<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupViewAction;
use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\Event;
use Illuminate\Contracts\View\View;

class ShowEditCupEventFormAction extends AbstractCupViewAction
{
    public function __invoke(Cup $cup, CupEvent $cupEvent): View
    {
        $events = Event::where('date', 'LIKE', "%{$cup->year}%")
            ->orderBy('date')
            ->get();

        return $this->view('cup.events.edit', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'events' => $events,
        ]);
    }
}
