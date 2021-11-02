<?php

namespace App\Http\Controllers\Flags;

use App\Models\Event;
use App\Models\Flag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowFlagEventsAction extends AbstractFlagsAction
{
    public function __invoke(Flag $flag): View|RedirectResponse
    {
        $events = Event::with(['protocolLines', 'competition'])
            ->orderByDesc('date')
            ->find($flag->events->pluck('id'));

        return $this->view('flags.events', [
            'flag' => $flag,
            'events' => $events,
        ]);
    }
}
