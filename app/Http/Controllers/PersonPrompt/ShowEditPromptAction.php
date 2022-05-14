<?php

namespace App\Http\Controllers\PersonPrompt;

use Illuminate\Contracts\View\View;

class ShowEditPromptAction extends AbstractPersonPromptAction
{
    public function __invoke(int $personId, int $promptId): View
    {
        $prompt = $this->promptService->getPrompt($promptId);

        return $this->view('person-prompt.edit', compact('personId', 'prompt'));
    }
}