<?php
declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Models\Flag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditEventFormAction extends AbstractEventAction
{
    public function __invoke(Event $event): View|RedirectResponse
    {
        return $this->view('events.edit', [
            'event' => $event,
            'flags' => Flag::all(),
        ]);
    }
}
