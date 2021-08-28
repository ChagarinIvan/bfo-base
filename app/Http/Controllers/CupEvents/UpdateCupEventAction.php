<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Cups\ShowCupAction;
use App\Models\Cup;
use App\Models\CupEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCupEventAction extends AbstractRedirectAction
{
    public function __invoke(Cup $cup, CupEvent $cupEvent, Request $request): RedirectResponse
    {
        $formData = $request->validate([
            'event' => 'required|numeric',
            'points' => 'required|numeric',
        ]);

        $cupEvent->event_id = $formData['event'];
        $cupEvent->cup_id = $cup->id;
        $cupEvent->points = $formData['points'];
        $cupEvent->save();

        return $this->redirector->action(ShowCupAction::class, [$cup]);
    }
}
