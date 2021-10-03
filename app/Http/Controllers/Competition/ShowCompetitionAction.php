<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Models\Competition;
use Illuminate\Contracts\View\View;

class ShowCompetitionAction extends AbstractCompetitionAction
{
    public function __invoke(Competition $competition): View
    {
        return $this->view('competitions.show', [
            'competition' => $competition,
            'events' => $competition->events->sortBy('date'),
        ]);
    }
}
