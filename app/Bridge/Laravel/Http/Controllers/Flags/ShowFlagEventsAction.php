<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Flags;

use App\Application\Dto\Event\SearchEventDto;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListLegacyEventsService;
use App\Models\Flag;
use Illuminate\Contracts\View\View;

class ShowFlagEventsAction extends AbstractFlagsAction
{
    public function __invoke(
        Flag $flag,
        ListLegacyEventsService $eventsService,
    ): View {
        $events = $eventsService->execute(new ListEvents(new SearchEventDto(flagId: (string) $flag->id)));

        /** @see /resources/views/flags/events.blade.php */
        return $this->view('flags.events', [
            'flag' => $flag,
            'events' => $events,
        ]);
    }
}
