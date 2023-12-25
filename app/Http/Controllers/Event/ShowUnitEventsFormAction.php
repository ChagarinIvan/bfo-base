<?php
declare(strict_types=1);

namespace App\Http\Controllers\Event;

use App\Models\Competition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowUnitEventsFormAction extends AbstractEventAction
{
    public function __invoke(Competition $competition): View|RedirectResponse
    {
        return $this->view('events.sum', [
            'competition' => $competition,
        ]);
    }
}
