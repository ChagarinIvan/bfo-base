<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Models\Year;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCompetitionsListAction extends AbstractCompetitionAction
{
    public function __invoke(int $yearInput): View|RedirectResponse
    {
        $year = Year::from($yearInput);
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
