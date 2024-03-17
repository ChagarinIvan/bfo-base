<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Dto\Event\EventSearchDto;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowUnitEventsFormAction extends BaseController
{
    use EventAction;

    public function __invoke(string $competitionId, ListEventsService $service): View
    {
        $events = $service->execute(new ListEvents(new EventSearchDto($competitionId)));

        /** @see /resources/views/events/sum.blade.php */
        return $this->view('events.sum', compact('competitionId', 'events'));
    }
}
