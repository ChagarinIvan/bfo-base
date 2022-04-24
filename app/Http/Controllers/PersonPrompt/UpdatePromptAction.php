<?php

namespace App\Http\Controllers\PersonPrompt;

use App\Http\Controllers\Person\ShowPersonPromptsListAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdatePromptAction extends AbstractPersonPromptAction
{
    public function __invoke(Request $request, int $promptId, int $personId): RedirectResponse
    {
        $formParams = $request->validate([
            'prompt' => 'required',
        ]);

        $prompt = $this->promptService->getPrompt($promptId);
        $prompt = $this->promptService->fillPrompt($prompt, $formParams);
        $this->promptService->storePersonPrompt($prompt);

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$personId]);
    }
}
