<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Http\Controllers\AbstractRedirectAction;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class AddFlagToEventAction extends AbstractRedirectAction
{
    public function __invoke(Event $event, int $flagId): RedirectResponse
    {
        $event->flags()->attach($flagId);
        return $this->redirector->action(ShowAddFlagToEventFormAction::class, [$event->id]);
    }
}
