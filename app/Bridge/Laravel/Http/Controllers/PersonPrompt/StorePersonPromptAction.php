<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPrompt\PersonPromptDto;
use App\Application\Service\PersonPrompt\AddPersonPrompt;
use App\Application\Service\PersonPrompt\AddPersonPromptService;
use App\Bridge\Laravel\Http\Controllers\Action;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class StorePersonPromptAction extends BaseController
{
    use Action;

    /**
     * @url /persons/prompt/{personId}/store
     */
    public function __invoke(
        string $personId,
        PersonPromptDto $prompt,
        AddPersonPromptService $service,
        UserId $userId,
    ): RedirectResponse {
        $service->execute(new AddPersonPrompt($prompt, $personId, $userId));

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$personId]);
    }
}
