<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use App\Http\Controllers\Error\Show404ErrorAction;
use Illuminate\Contracts\View\View;

class ShowCupEventGroupAction extends AbstractCupAction
{
    public function __invoke(int $cupId, int $cupEventId, int $groupId): View
    {
        $group = $this->groupsRepository->getGroup($groupId);
        $cup = $this->cupsRepository->getCup($cupId);
        $cupEvent = $this->cupEventsService->getCupEvent($cupEventId);

        if ($group === null || $cup === null || $cupEvent === null) {
            $this->redirector->action(Show404ErrorAction::class);
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
