<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cups;

use App\Models\Cup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class ShowCupAction extends AbstractCupAction
{
    public function __invoke(Cup $cup): View|RedirectResponse
    {
        $cupEvents = $this->cupEventsService->getCupEvents($cup);
        $cupEvents = $cupEvents->sortBy('event.date')->values();

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
