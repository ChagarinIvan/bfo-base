<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Competition\AbstractCompetitionViewAction;
use App\Models\Event;
use App\Models\Flag;
use Illuminate\Contracts\View\View;

class ShowAddFlagToEventFormAction extends AbstractCompetitionViewAction
{
    public function __invoke(Event $event): View
    {
        return $this->view('events.add-flags', [
            'event' => $event,
            'flags' => Flag::all(),
        ]);
    }
}
