<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Bridge\Laravel\Http\Controllers\Action;
use App\Services\PersonPromptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class DeletePromptAction extends BaseController
{
    use Action;

    public function __invoke(string $promptId, PersonPromptService $service): RedirectResponse
    {
        $prompt = $service->getPrompt((int) $promptId);
        $service->deletePersonPrompt((int) $promptId);

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$prompt->person_id]);
    }
}
