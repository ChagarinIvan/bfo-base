<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use App\Models\Group;
use App\Models\Person;
use Illuminate\Contracts\View\View;

class ShowCupTableAction extends AbstractCupAction
{
    public function __invoke(Cup $cup, Group $group): View
    {
        $cupType = $cup->getCupType();

        $cupEvents = $this->cupEventsService->getCupEvents($cup)->sortBy('events.date');
        $cupPoints = $cupType->calculateCup($cup, $cupEvents, $group);

        return $this->view('cup.table', [
            'cup' => $cup,
            'cupEvents' => $cupEvents,
            'cupPoints' => $cupPoints,
            'persons' => Person::whereIn('id', array_keys($cupPoints))->get()->keyBy('id'),
            'activeGroup' => $group,
        ]);
    }
}
