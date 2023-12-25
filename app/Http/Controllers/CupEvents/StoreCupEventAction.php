<?php
declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use App\Http\Controllers\Cups\ShowCupAction;
use App\Models\Cup;
use App\Models\CupEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreCupEventAction extends AbstractCupAction
{
    public function __invoke(Cup $cup, Request $request): RedirectResponse
    {
        $formData = $request->validate([
            'event' => 'required|numeric',
            'points' => 'required|numeric',
        ]);

        $cupEvent = new CupEvent();
        $cupEvent->event_id = $formData['event'];
        $cupEvent->cup_id = $cup->id;
        $cupEvent->points = $formData['points'];
        $this->cupEventsService->storeCupEvent($cupEvent);
        $this->cupsService->clearCupCache($cupEvent->cup_id);

        return $this->redirector->action(ShowCupAction::class, [$cup]);
    }
}
