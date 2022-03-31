<?php

namespace App\Http\Controllers\Competition;

use App\Models\Year;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCompetitionsListAction extends AbstractCompetitionAction
{
    public function __invoke(Year $year): View|RedirectResponse
    {
        return $this->view('competitions.index', [
            'competitions' => $this->competitionService->getYearCompetitions($year),
            'selectedYear' => $year,
        ], Year::actualYear() === $year);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}