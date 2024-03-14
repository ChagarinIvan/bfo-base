<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use App\Application\Dto\Event\EventSearchDto;
use App\Application\Service\Competition\ViewCompetition;
use App\Application\Service\Competition\ViewCompetitionService;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

final class ShowCompetitionAction extends BaseController
{
    use CompetitionAction;

    public function __invoke(
        string $competitionId,
        ViewCompetitionService $competitionService,
        ListEventsService $eventsService,
    ): View {
        $competition = $competitionService->execute(new ViewCompetition($competitionId));
        $eventSearchDto = new EventSearchDto();
        $eventSearchDto->competitionId = $competitionId;
        $events = $eventsService->execute(new ListEvents($eventSearchDto));

        /** @see /resources/views/competitions/show.blade.php */
        return $this->view('competitions.show', [
            'competition' => $competition,
            'events' => $events,
        ]);
    }
}
