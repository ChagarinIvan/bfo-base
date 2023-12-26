<?php

declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Models\Flag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCreateEventFormAction extends AbstractEventAction
{
    public function __invoke(string $competitionId): View|RedirectResponse
    {
        return $this->view('events.create', [
            'competitionId' => $competitionId,
            'flags' => Flag::all(),
        ]);
    }
}
