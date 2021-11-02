<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\AbstractAction;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class DeleteEventFlagAction extends AbstractAction
{
    public function __invoke(Event $event, int $flagId): RedirectResponse
    {
        $event->flags()->detach($flagId);
        return $this->redirector->action(ShowAddFlagToEventFormAction::class, [$event->id]);
    }
}
