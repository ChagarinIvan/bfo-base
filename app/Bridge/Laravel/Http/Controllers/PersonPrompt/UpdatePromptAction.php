<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Bridge\Laravel\Http\Controllers\Action;
use App\Services\PersonPromptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class UpdatePromptAction extends BaseController
{
    use Action;

    public function __invoke(string $promptId, Request $request, PersonPromptService $service): RedirectResponse
    {
        $formParams = $request->validate([
            'prompt' => 'required',
        ]);

        $prompt = $service->getPrompt((int) $promptId);
        $prompt = $service->fillPrompt($prompt, $formParams);
        $service->storePersonPrompt($prompt);

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$prompt->person_id]);
    }
}
