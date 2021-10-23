<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Models\Distance;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEventAction extends AbstractEventAction
{
    public function __invoke(Event $event, Distance $distance): View|RedirectResponse
    {
        $withPoints = false;
        $protocolLines = $event->protocolLines()->where('distance_id', $distance->id)->get();

        foreach ($protocolLines as $protocolLine) {
            $withPoints = $protocolLine->points !== null;
            if ($withPoints) {
                break;
            }
        }

        return $this->view('events.show_others', [
            'event' => $event,
            'lines' => $protocolLines,
            'withPoints' => $withPoints,
            'selectedDistance' => $distance,
        ]);
    }
}
