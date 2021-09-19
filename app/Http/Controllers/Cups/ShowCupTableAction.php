<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use App\Models\Group;
use App\Models\Person;
use Illuminate\Contracts\View\View;

class ShowCupTableAction extends AbstractCupViewAction
{
    public function __invoke(Cup $cup, Group $group): View
    {
        $cupType = $cup->getCupType();

        $events = $cup->events()
            ->with(['cup'])
            ->join('events', 'events.id', '=', 'cup_events.event_id')
            ->orderBy('events.date')
            ->get();

        $cupPoints = $cupType->calculate($cup, $events, $group);

        return $this->view('cup.table', [
            'cup' => $cup,
            'events' => $events,
            'cupPoints' => $cupPoints,
            'persons' => Person::whereIn('id', array_keys($cupPoints))->get()->keyBy('id'),
            'activeGroup' => $group,
        ]);
    }
}
