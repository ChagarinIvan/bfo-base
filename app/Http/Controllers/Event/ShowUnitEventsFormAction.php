<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Competition\AbstractCompetitionViewAction;
use App\Models\Competition;
use Illuminate\Contracts\View\View;

class ShowUnitEventsFormAction extends AbstractCompetitionViewAction
{
    public function __invoke(Competition $competition): View
    {
        return $this->view('events.sum', [
            'competition' => $competition,
        ]);
    }
}
