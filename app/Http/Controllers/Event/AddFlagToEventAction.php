<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Http\Controllers\AbstractAction;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class AddFlagToEventAction extends AbstractAction
{
    public function __invoke(Event $event, int $flagId): RedirectResponse
    {
        $event->flags()->attach($flagId);
        return $this->redirector->action(ShowAddFlagToEventFormAction::class, [$event->id]);
    }
}
