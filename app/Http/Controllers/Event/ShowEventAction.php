<?php

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
        $withVk = false;
        $protocolLines = $distance->protocolLines;

        foreach ($protocolLines as $protocolLine) {
            $withPoints = $withPoints || $protocolLine->points !== null;
            $withVk = $withVk || $protocolLine->vk;
            if ($withPoints && $withVk) {
                break;
            }
        }

        return $this->view('events.show', [
            'event' => $event,
            'lines' => $protocolLines,
            'withPoints' => $withPoints,
            'withVk' => $withVk,
            'selectedDistance' => $distance,
        ]);
    }
}
