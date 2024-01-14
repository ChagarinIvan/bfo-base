<?php

declare(strict_types=1);

namespace App\Http\Controllers\PersonPrompt;

use Illuminate\Contracts\View\View;
use function compact;

class ShowEditPromptAction extends AbstractPersonPromptAction
{
    public function __invoke(string $personId, string $promptId): View
    {
        $prompt = $this->promptService->getPrompt((int) $promptId);

        return $this->view('person-prompt.edit', compact('personId', 'prompt'));
    }
}
