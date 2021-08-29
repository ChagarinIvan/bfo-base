<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\AbstractRedirectAction;
use App\Models\Competition;
use App\Models\Distance;
use App\Models\Event;
use App\Models\ProtocolLine;
use Illuminate\Http\RedirectResponse;

class DeleteCompetitionAction extends AbstractRedirectAction
{
    public function __invoke(int $year, int $competitionId): RedirectResponse
    {
        Competition::destroy($competitionId);
        $events = Event::whereCompetitionId($competitionId)->get();
        $eventsIds = $events->pluck('id');
        Event::destroy($eventsIds);
        $distances = Distance::whereIn('event_id', $eventsIds)->get();
        $distancesIds = $distances->pluck('id');
        Distance::destroy($distancesIds);
        ProtocolLine::whereIn('distance_id', $distancesIds)->get();
        return $this->redirector->action(ShowCompetitionsListAction::class, ['year' => $year]);
    }
}
