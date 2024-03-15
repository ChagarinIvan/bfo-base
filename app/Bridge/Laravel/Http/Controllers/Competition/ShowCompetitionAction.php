<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use App\Application\Dto\Event\EventSearchDto;
use App\Application\Service\Competition\Exception\CompetitionNotFound;
use App\Application\Service\Competition\ViewCompetition;
use App\Application\Service\Competition\ViewCompetitionService;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

final class ShowCompetitionAction extends BaseController
{
    use CompetitionAction;

    public function __invoke(
        string $competitionId,
        ViewCompetitionService $competitionService,
        ListEventsService $eventsService,
    ): View|RedirectResponse {
        try {
            $competition = $competitionService->execute(new ViewCompetition($competitionId));
        } catch (CompetitionNotFound) {
            return $this->redirectTo404Error();
        }

        $events = $eventsService->execute(new ListEvents(new EventSearchDto($competitionId)));

        /** @see /resources/views/competitions/show.blade.php */
        return $this->view('competitions.show', [
            'competition' => $competition,
            'events' => $events,
        ]);
    }
}
