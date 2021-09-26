<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Competition\ShowCompetitionAction;
use App\Models\Event;
use App\Services\BackUrlService;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class DeleteEventAction extends AbstractRedirectAction
{
    private EventService $eventService;

    public function __construct(Redirector $redirector, BackUrlService $backUrlService, EventService $eventService)
    {
        parent::__construct($redirector, $backUrlService);
        $this->eventService = $eventService;
    }

    public function __invoke(Event $event): RedirectResponse
    {
        $this->eventService->deleteEvent($event);
        return $this->redirector->action(ShowCompetitionAction::class, [$event->competition_id]);
    }
}
