<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use function compact;

class ShowEditCompetitionFormAction extends AbstractCompetitionAction
{
    public function __invoke(string $year, string $competitionId): View|RedirectResponse
    {
        $competition = $this->competitionService->getCompetition((int) $competitionId);

        return $this->view('competitions.edit', compact('year', 'competition'));
    }
}
