<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupViewAction;
use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\Group;
use Illuminate\Contracts\View\View;

class ShowCupEventGroupAction extends AbstractCupViewAction
{
    public function __invoke(string $cup, string $cupEvent, string $group): View
    {
        $group = Group::find($group);
        $cup = Cup::find($cup);
        $cupEvent = CupEvent::find($cupEvent);
        $cupType = $cup->cupType();
        $cupEventPoints = $cupType->calculateEvent($cupEvent, $group);

        return $this->view('cup.events.show', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'cupEventPoints' => $cupEventPoints,
            'groupId' => $group->id,
        ]);
    }
}
