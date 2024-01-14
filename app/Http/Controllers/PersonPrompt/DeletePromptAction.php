<?php

declare(strict_types=1);

namespace App\Http\Controllers\PersonPrompt;

use App\Http\Controllers\Person\ShowPersonPromptsListAction;
use Illuminate\Http\RedirectResponse;

class DeletePromptAction extends AbstractPersonPromptAction
{
    public function __invoke(string $personId, string $promptId): RedirectResponse
    {
        $this->promptService->deletePersonPrompt((int) $promptId);

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$personId]);
    }
}
