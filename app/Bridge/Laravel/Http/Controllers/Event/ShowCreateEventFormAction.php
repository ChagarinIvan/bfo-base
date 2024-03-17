<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowCreateEventFormAction extends BaseController
{
    use EventAction;

    public function __invoke(string $competitionId): View
    {
        /** @see /resources/views/events/create.blade.php */
        return $this->view('events.create', compact('competitionId'));
    }
}
