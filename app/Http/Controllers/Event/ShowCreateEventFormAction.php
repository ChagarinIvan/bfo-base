<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Competition\AbstractCompetitionViewAction;
use App\Models\Flag;
use Illuminate\Contracts\View\View;

class ShowCreateEventFormAction extends AbstractCompetitionViewAction
{
    public function __invoke(int $competitionId): View
    {
        return $this->view('events.create', [
            'competitionId' => $competitionId,
            'flags' => Flag::all(),
        ]);
    }
}
