<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\PersonPrompt\DeletePersonPrompt;
use App\Application\Service\PersonPrompt\DeletePersonPromptService;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Bridge\Laravel\Http\Controllers\Action;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class DeletePromptAction extends BaseController
{
    use Action;

    /**
     * @url /persons/prompt/{promptId}/delete
     */
    public function __invoke(
        string $promptId,
        DeletePersonPromptService $service,
        UserId $userId,
    ): RedirectResponse {
        try {
            // гэта поўнае выдаленне, трэба дарабіць выдаленне праз дэактывацыю "soft-delete"
            $prompt = $service->execute(new DeletePersonPrompt($promptId));
        } catch (PersonPromptNotFound) {
            return $this->redirectTo404Error();
        }

        return $this->redirector->action(ShowPersonPromptsListAction::class, [$prompt->personId]);
    }
}
