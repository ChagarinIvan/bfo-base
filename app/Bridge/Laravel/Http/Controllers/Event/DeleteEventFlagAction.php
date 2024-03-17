<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use App\Domain\Event\Event;
use Illuminate\Http\RedirectResponse;

class DeleteEventFlagAction extends AbstractAction
{
    public function __invoke(Event $event, string $flagId): RedirectResponse
    {
        $event->flags()->detach($flagId);
        return $this->redirector->action(ShowAddFlagToEventFormAction::class, [$event->id]);
    }
}
