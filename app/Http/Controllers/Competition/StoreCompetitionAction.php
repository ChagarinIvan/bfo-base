<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Models\Competition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreCompetitionAction extends AbstractCompetitionAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $competition = new Competition($formParams);
        $competition->save();

        return $this->redirector->action(ShowCompetitionAction::class, [$competition->id]);
    }
}
