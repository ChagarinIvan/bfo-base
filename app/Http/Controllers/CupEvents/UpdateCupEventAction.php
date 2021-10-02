<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Cups\ShowCupAction;
use App\Http\Controllers\Error\Show404ErrorAction;
use App\Services\BackUrlService;
use App\Services\CupEventsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class UpdateCupEventAction extends AbstractRedirectAction
{
    private CupEventsService $cupEventsService;

    public function __construct(
        CupEventsService $cupEventsService,
        Redirector       $redirector,
        BackUrlService   $backUrlService
    ) {
        parent::__construct($redirector, $backUrlService);
        $this->cupEventsService = $cupEventsService;
    }

    public function __invoke(int $cupId, int $cupEventId, Request $request): RedirectResponse
    {
        $formData = $request->validate([
            'event' => 'required|numeric',
            'points' => 'required|numeric',
        ]);

        $cupEvent = $this->cupEventsService->getCupEvent($cupEventId);
        if ($cupEvent === null) {
            $this->redirector->action(Show404ErrorAction::class);
        }

        $cupEvent->event_id = $formData['event'];
        $cupEvent->cup_id = $cupId;
        $cupEvent->points = $formData['points'];
        $this->cupEventsService->storeCupEvent($cupEvent);

        return $this->redirector->action(ShowCupAction::class, [$cupId]);
    }
}
