<?php

declare(strict_types=1);

namespace App\Http\Controllers\PersonPrompt;

use App\Http\Controllers\Person\ShowPersonPromptsListAction;
use App\Models\PersonPrompt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorePromptAction extends AbstractPersonPromptAction
{
    public function __invoke(Request $request, string $personId): RedirectResponse
    {
        $formParams = $request->validate([
            'prompt' => 'required',
        ]);

        $prompt = $this->promptService->fillPrompt(new PersonPrompt(), $formParams, (int) $personId);
        $this->promptService->storePersonPrompt($prompt);

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$personId]);
    }
}
