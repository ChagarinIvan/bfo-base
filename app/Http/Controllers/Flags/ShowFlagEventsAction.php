<?php

declare(strict_types=1);

namespace App\Http\Controllers\Flags;

use App\Models\Event;
use App\Models\Flag;
use Illuminate\Contracts\View\View;

class ShowFlagEventsAction extends AbstractFlagsViewAction
{
    public function __invoke(Flag $flag): View
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
