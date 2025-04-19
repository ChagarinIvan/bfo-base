<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Flags;

use App\Domain\Event\Event;
use App\Models\Flag;
use Illuminate\Contracts\View\View;

class ShowFlagEventsAction extends AbstractFlagsAction
{
    public function __invoke(Flag $flag): View
    {
        $events = Event::with(['protocolLines', 'competition'])
            ->orderByDesc('date')
            ->find($flag->events->pluck('id'))
        ;

        /** @see /resources/views/flags/events.blade.php */
        return $this->view('flags.events', [
            'flag' => $flag,
            'events' => $events,
        ]);
    }
}
