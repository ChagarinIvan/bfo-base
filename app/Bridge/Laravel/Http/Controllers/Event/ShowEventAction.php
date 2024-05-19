<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Event\Event;
use Illuminate\Contracts\View\View;

class ShowEventAction extends AbstractEventAction
{
    public function __invoke(string $eventId): View
    {
        $event = Event::findOrFail($eventId);
        $withPoints = false;
        $withVk = false;
        $distance = $event->distances->first();
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
            'event' => $event,
            'lines' => $protocolLines,
            'withPoints' => $withPoints,
            'withVk' => $withVk,
            'selectedDistance' => $distance,
            'clubs' => $clubs,
        ]);
    }
}
