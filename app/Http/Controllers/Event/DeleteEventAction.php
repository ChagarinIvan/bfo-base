<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Competition\ShowCompetitionAction;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class DeleteEventAction extends AbstractRedirectAction
{
    public function __invoke(Event $event): RedirectResponse
    {
        $competitionId = $event->competition_id;
        $event->distances()->delete();
        $event->protocolLines()->delete();
        $event->delete();
        return $this->redirector->action(ShowCompetitionAction::class, [$competitionId]);
    }
}
