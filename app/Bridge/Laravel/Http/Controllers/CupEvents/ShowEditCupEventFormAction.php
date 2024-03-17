<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Application\Dto\Event\EventSearchDto;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use App\Bridge\Laravel\Http\Controllers\Cups\AbstractCupAction;
use Illuminate\Contracts\View\View;
use function compact;

class ShowEditCupEventFormAction extends AbstractCupAction
{
    public function __invoke(
        string $cupId,
        string $cupEventId,
        ListEventsService $listEvents,
    ): View {
        $cup = $this->cupsService->getCup((int) $cupId);
        $cupEvent = $this->cupEventsService->getCupEvent((int) $cupEventId);
        $events = $listEvents->execute(new ListEvents(new EventSearchDto(year: (string) $cup->year, )));

        /** @see /resources/views/cup/events/edit.blade.php */
        return $this->view('cup.events.edit', compact('cup', 'cupEvent', 'events'));
    }
}
