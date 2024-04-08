<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Domain\Event\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEventAction extends AbstractEventAction
{
    public function __invoke(Event $event): View|RedirectResponse
    {
        dump($event);
        $withPoints = false;
        $withVk = false;
        $distance = $event->distances->first();
        dump($distance);
        $protocolLines = $distance->protocolLines;
        $clubs = $this->clubsService->getAllClubs()->keyBy('normalize_name');

        foreach ($protocolLines as $protocolLine) {
            $withPoints = $withPoints || $protocolLine->points !== null;
            $withVk = $withVk || $protocolLine->vk;
            if ($withPoints && $withVk) {
                break;
            }
        }

        /** @see /resources/views/events/show.blade.php */
        return $this->view('events.show', [
            'event' => $distance->event,
            'lines' => $protocolLines,
            'withPoints' => $withPoints,
            'withVk' => $withVk,
            'selectedDistance' => $distance,
            'clubs' => $clubs,
        ]);
    }
}
