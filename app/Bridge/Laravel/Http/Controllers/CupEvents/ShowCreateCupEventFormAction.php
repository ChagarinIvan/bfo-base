<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Application\Dto\Event\EventSearchDto;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use App\Bridge\Laravel\Http\Controllers\Cups\AbstractCupAction;
use App\Models\Cup;
use Illuminate\Contracts\View\View;

class ShowCreateCupEventFormAction extends AbstractCupAction
{
    public function __invoke(Cup $cup, ListEventsService $listEvents): View
    {
        $events = $listEvents->execute(new ListEvents(new EventSearchDto(
            year: (string) $cup->year,
            idNotIn: $cup->events->pluck('event_id')->all(),
        )));

        /** @see /resources/views/cup/events/create.blade.php */
        return $this->view('cup.events.create', [
            'cup' => $cup,
            'events' => $events,
        ]);
    }
}
