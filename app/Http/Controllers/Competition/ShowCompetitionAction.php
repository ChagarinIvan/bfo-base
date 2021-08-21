<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\AbstractViewAction;
use App\Models\Competition;
use App\Models\Event;
use Illuminate\Contracts\View\View;

class ShowCompetitionAction extends AbstractViewAction
{
    public function __invoke(int $competitionId): View
    {
        $competition = Competition::find($competitionId);
        $events = Event::whereCompetitionId($competitionId)
            ->orderBy('date')
            ->get();

        return $this->viewFactory->make('competitions.show', [
            'competition' => $competition,
            'events' => $events,
        ]);
    }
}
