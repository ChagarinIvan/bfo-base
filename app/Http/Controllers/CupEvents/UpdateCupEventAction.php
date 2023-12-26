<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use App\Http\Controllers\Cups\ShowCupAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCupEventAction extends AbstractCupAction
{
    public function __invoke(string $cupId, string $cupEventId, Request $request): RedirectResponse
    {
        $formData = $request->validate([
            'event' => 'required|numeric',
            'points' => 'required|numeric',
        ]);

        $cupEvent = $this->cupEventsService->getCupEvent((int) $cupEventId);
        $cupEvent->event_id = $formData['event'];
        $cupEvent->cup_id = (int) $cupId;
        $cupEvent->points = $formData['points'];
        $this->cupEventsService->storeCupEvent($cupEvent);

        return $this->redirector->action(ShowCupAction::class, [$cupId]);
    }
}
