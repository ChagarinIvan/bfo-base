<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Application\Service\PersonPrompt\ViewPersonPrompt;
use App\Application\Service\PersonPrompt\ViewPersonPromptService;
use App\Bridge\Laravel\Http\Controllers\Person\PersonAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowEditPromptAction extends BaseController
{
    use PersonAction;

    /**
     * @url /persons/prompt/{promptId}/edit
     */
    public function __invoke(string $promptId, ViewPersonPromptService $service): View|RedirectResponse
    {
        try {
            $prompt = $service->execute(new ViewPersonPrompt($promptId));
        } catch (PersonPromptNotFound) {
            return $this->redirectTo404Error();
        }

        /** @see /resources/views/person-prompt/edit.blade.php */
        return $this->view('person-prompt.edit', ['prompt' => $prompt]);
    }
}
