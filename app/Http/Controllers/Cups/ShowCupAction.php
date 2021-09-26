<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use App\Services\CupEventsService;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class ShowCupAction extends AbstractCupViewAction
{
    private CupEventsService $cupEventsService;

    public function __construct(ViewActionsService $viewService, CupEventsService $cupEventsService)
    {
        parent::__construct($viewService);
        $this->cupEventsService = $cupEventsService;
    }

    public function __invoke(Cup $cup): View
    {
        $cupEvents = $this->cupEventsService->getCupEvents($cup);
        $cupEvents->sortBy('event.date')->values();

        $cupEventsParticipateCount = Collection::empty();
        foreach ($cupEvents as $cupEvent) {
            $cupEventsParticipateCount->put($cupEvent->id, $this->cupEventsService->getCupEventPersonsCount($cupEvent));
        }

        return $this->view('cup.show', [
            'cup' => $cup,
            'cupEvents' => $cupEvents,
            'cupEventsParticipateCount' => $cupEventsParticipateCount,
        ]);
    }
}
