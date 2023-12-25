<?php

declare(strict_types=1);

namespace App\Http\Controllers\PersonPrompt;

use App\Http\Controllers\Person\ShowPersonPromptsListAction;
use Illuminate\Http\RedirectResponse;

class DeletePromptAction extends AbstractPersonPromptAction
{
    public function __invoke(int $personId, int $promptId): RedirectResponse
    {
        $this->promptService->deletePersonPrompt($promptId);

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$personId]);
    }
}
