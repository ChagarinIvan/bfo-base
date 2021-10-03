<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Models\Flag;
use Illuminate\Contracts\View\View;

class ShowEditEventFormAction extends AbstractEventAction
{
    public function __invoke(Event $event): View
    {
        return $this->view('events.edit', [
            'event' => $event,
            'flags' => Flag::all(),
        ]);
    }
}
