<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Bridge\Laravel\Http\Controllers\Cup\CupAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupAction;
use App\Domain\CupEvent\CupEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class StoreCupEventAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        Request $request,
    ): RedirectResponse {
        $formData = $request->validate([
            'event' => 'required|numeric',
            'points' => 'required|numeric',
        ]);

        $cupEvent = new CupEvent;
        $cupEvent->event_id = $formData['event'];
        $cupEvent->cup_id = (int) $cupId;
        $cupEvent->points = $formData['points'];
        $cupEvent->save();

        return $this->redirector->action(ShowCupAction::class, [$cupId]);
    }
}
