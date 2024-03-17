<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Bridge\Laravel\Http\Controllers\Action;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Services\PersonPromptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class StorePromptAction extends BaseController
{
    use Action;

    public function __invoke(Request $request, string $personId, PersonPromptService $service): RedirectResponse
    {
        $formParams = $request->validate([
            'prompt' => 'required',
        ]);

        $prompt = $service->fillPrompt(new PersonPrompt(), $formParams, (int) $personId);
        $service->storePersonPrompt($prompt);

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$personId]);
    }
}
