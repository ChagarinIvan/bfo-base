<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Dto\Event\SearchEventDto;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListLegacyEventsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

class ShowUnitEventsFormAction extends BaseController
{
    use EventAction;

    public function __invoke(string $competitionId, ListLegacyEventsService $service): View
    {
        $events = $service->execute(new ListEvents(new SearchEventDto($competitionId)));

        /** @see /resources/views/events/sum.blade.php */
        return $this->view('events.sum', ['competitionId' => $competitionId, 'events' => $events]);
    }
}
