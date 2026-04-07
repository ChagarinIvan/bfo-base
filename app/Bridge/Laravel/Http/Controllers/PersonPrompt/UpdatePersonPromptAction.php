<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPrompt\PersonPromptDto;
use App\Application\Service\PersonPrompt\AddPersonPrompt;
use App\Application\Service\PersonPrompt\AddPersonPromptService;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Application\Service\PersonPrompt\UpdatePersonPrompt;
use App\Application\Service\PersonPrompt\UpdatePersonPromptService;
use App\Bridge\Laravel\Http\Controllers\Action;
use App\Services\PersonPromptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class UpdatePersonPromptAction extends BaseController
{
    use Action;

    /**
     * @url /persons/prompt/{promptId}/update
     */
    public function __invoke(
        string $promptId,
        PersonPromptDto $prompt,
        UpdatePersonPromptService $service,
        UserId $userId,
    ): RedirectResponse {
        try {
            $dto = $service->execute(new UpdatePersonPrompt($prompt, $promptId, $userId));
        } catch (PersonPromptNotFound) {
            return $this->redirectTo404Error();
        }

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$dto->personId]);
    }
}
