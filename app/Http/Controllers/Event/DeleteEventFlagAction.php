<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Http\Controllers\AbstractRedirectAction;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class DeleteEventFlagAction extends AbstractRedirectAction
{
    public function __invoke(Event $event, int $flagId): RedirectResponse
    {
        $event->flags()->detach($flagId);
        return $this->redirector->action(ShowAddFlagToEventFormAction::class, [$event->id]);
    }
}
