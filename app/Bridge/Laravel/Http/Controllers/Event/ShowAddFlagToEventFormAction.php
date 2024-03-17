<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Domain\Event\Event;
use App\Models\Flag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowAddFlagToEventFormAction extends AbstractEventAction
{
    public function __invoke(Event $event): View|RedirectResponse
    {
        return $this->view('events.add-flags', [
            'event' => $event,
            'flags' => Flag::all(),
        ]);
    }
}
