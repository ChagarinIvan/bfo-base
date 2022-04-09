<?php

namespace App\Http\Controllers\Competition;

use App\Models\Competition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCompetitionAction extends AbstractCompetitionAction
{
    public function __invoke(int $year, Competition $competition, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $competition = $this->competitionService->fillAndStore($competition, $formParams);

        return $this->redirector->action(ShowCompetitionAction::class, [$competition->id]);
    }
}
