<?php
declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCompetitionAction extends AbstractCompetitionAction
{
    public function __invoke(int $year, int $competitionId, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $competition = $this->competitionService->getCompetition($competitionId);
        $competition = $this->competitionService->fillAndStore($competition, $formParams);

        return $this->redirector->action(ShowCompetitionAction::class, [$competition->id]);
    }
}
