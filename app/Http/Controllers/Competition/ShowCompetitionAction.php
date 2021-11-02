<?php

namespace App\Http\Controllers\Competition;

use App\Models\Competition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCompetitionAction extends AbstractCompetitionAction
{
    public function __invoke(Competition $competition): View|RedirectResponse
    {
        return $this->view('competitions.show', [
            'competition' => $competition,
            'events' => $competition->events->sortBy('date'),
        ]);
    }
}
