<?php

namespace App\Http\Controllers\PersonPrompt;

use App\Http\Controllers\Person\ShowPersonPromptsListAction;
use App\Models\PersonPrompt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorePromptAction extends AbstractPersonPromptAction
{
    public function __invoke(Request $request, int $personId): RedirectResponse
    {
        $formParams = $request->validate([
            'prompt' => 'required',
        ]);

        $prompt = $this->promptService->fillPrompt(new PersonPrompt(), $formParams, $personId);
        $this->promptService->storePersonPrompt($prompt);

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$personId]);
    }
}
