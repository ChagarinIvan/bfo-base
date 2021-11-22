<?php

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCupEventGroupAction extends AbstractCupAction
{
    public function __invoke(int $cupId, int $cupEventId, int $groupId): View|RedirectResponse
    {
        try {
            $group = $this->groupsService->getGroup($groupId);
            $cup = $this->cupsService->getCup($cupId);
            $cupEvent = $this->cupEventsService->getCupEvent($cupEventId);
        } catch (\RuntimeException) {
            return $this->redirectTo404Error();
        }

        $cupType = $cup->getCupType();
        $cupEventPoints = $cupType->calculateEvent($cupEvent, $group);

        return $this->view('cup.events.show', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'cupEventPoints' => $cupEventPoints,
            'groupId' => $group->id,
        ]);
    }
}
