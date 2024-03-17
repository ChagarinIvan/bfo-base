<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Application\Service\PersonPrompt\ViewPersonPrompt;
use App\Application\Service\PersonPrompt\ViewPersonPromptService;
use App\Bridge\Laravel\Http\Controllers\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowEditPromptAction extends BaseController
{
    use Action;

    public function __invoke(string $promptId, ViewPersonPromptService $service): View
    {
        $prompt = $service->execute(new ViewPersonPrompt($promptId));

        /** @see /resources/views/person-prompt/edit.blade.php */
        return $this->view('person-prompt.edit', compact('prompt'));
    }
}
