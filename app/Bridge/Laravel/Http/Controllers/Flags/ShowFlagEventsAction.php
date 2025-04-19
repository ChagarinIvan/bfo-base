<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Flags;

use App\Application\Dto\Event\EventSearchDto;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use App\Models\Flag;
use Illuminate\Contracts\View\View;

class ShowFlagEventsAction extends AbstractFlagsAction
{
    public function __invoke(
        Flag $flag,
        ListEventsService $eventsService,
    ): View {
        $events = $eventsService->execute(new ListEvents(new EventSearchDto(flagId: (string) $flag->id)));

        /** @see /resources/views/flags/events.blade.php */
        return $this->view('flags.events', [
            'flag' => $flag,
            'events' => $events,
        ]);
    }
}
