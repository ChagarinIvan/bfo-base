<?php

namespace App\Http\Controllers\PersonPrompt;

use App\Http\Controllers\Person\ShowPersonPromptsListAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DeletePromptAction extends AbstractPersonPromptAction
{
    public function __invoke(int $personId, int $promptId): View|RedirectResponse
    {
        $this->promptService->deletePersonPrompt($promptId);

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$personId]);
    }
}
