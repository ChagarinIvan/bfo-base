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
    public function __invoke(Cup $cup, CupEvent $cupEvent, Group $group): View
    {
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
