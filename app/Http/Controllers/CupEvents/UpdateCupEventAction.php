<?php

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use App\Http\Controllers\Cups\ShowCupAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCupEventAction extends AbstractCupAction
{
    public function __invoke(int $cupId, int $cupEventId, Request $request): RedirectResponse
    {
        $formData = $request->validate([
            'event' => 'required|numeric',
            'points' => 'required|numeric',
        ]);

        $cupEvent = $this->cupEventsService->getCupEvent($cupEventId);
        if ($cupEvent === null) {
            return $this->redirectToError();
        }

        $cupEvent->event_id = $formData['event'];
        $cupEvent->cup_id = $cupId;
        $cupEvent->points = $formData['points'];
        $this->cupEventsService->storeCupEvent($cupEvent);

        return $this->redirector->action(ShowCupAction::class, [$cupId]);
    }
}
