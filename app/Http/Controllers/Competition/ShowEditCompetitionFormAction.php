<?php

namespace App\Http\Controllers\Competition;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditCompetitionFormAction extends AbstractCompetitionAction
{
    public function __invoke(int $competitionId): View|RedirectResponse
    {
        $competition = $this->competitionService->getCompetition($competitionId);

        return $this->view('competitions.edit', compact('competition'));
    }
}
