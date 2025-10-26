<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionAction;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEventAction extends AbstractEventAction
{
    public function __invoke(string $eventId): View|RedirectResponse
    {
        /** @var Event $event */
        $event = Event::findOrFail($eventId);
        $withPoints = false;
        $withVk = false;
        /** @var Distance|null $distance */
        $distance = $event->distances->first();

        if ($distance === null) {
            return $this->redirector->action(ShowCompetitionAction::class, [$event->competition_id]);
        }

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
