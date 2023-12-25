<?php

declare(strict_types=1);

namespace App\Http\Controllers\PersonPrompt;

use App\Http\Controllers\Person\ShowPersonPromptsListAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdatePromptAction extends AbstractPersonPromptAction
{
    public function __invoke(int $personId, int $promptId, Request $request): RedirectResponse
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
