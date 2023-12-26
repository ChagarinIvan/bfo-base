<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use Illuminate\Http\RedirectResponse;

class DeleteCompetitionAction extends AbstractCompetitionAction
{
    public function __invoke(string $year, int $competitionId): RedirectResponse
    {
        $competition = $this->competitionService->getCompetition($competitionId);
        $this->competitionService->deleteCompetition($competition);

        return $this->redirector->action(ShowCompetitionsListAction::class, [$year]);
    }
}
