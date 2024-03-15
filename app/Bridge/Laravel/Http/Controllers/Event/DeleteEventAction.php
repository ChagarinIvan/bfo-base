<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionAction;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class DeleteEventAction extends AbstractEventAction
{
    public function __invoke(Event $event): RedirectResponse
    {
        $this->eventService->deleteEvent($event);
        return $this->redirector->action(ShowCompetitionAction::class, [$event->competition_id]);
    }
}