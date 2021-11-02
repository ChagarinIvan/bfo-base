<?php

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCupEventGroupAction extends AbstractCupAction
{
    public function __invoke(int $cupId, int $cupEventId, int $groupId): View|RedirectResponse
    {
        $group = $this->groupsService->getGroup($groupId);
        $cup = $this->cupsRepository->getCup($cupId);
        $cupEvent = $this->cupEventsService->getCupEvent($cupEventId);

        if ($group === null || $cup === null || $cupEvent === null) {
            return $this->redirectToError();
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
