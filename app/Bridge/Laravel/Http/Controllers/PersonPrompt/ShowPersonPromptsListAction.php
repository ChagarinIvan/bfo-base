<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Application\Dto\PersonPrompt\SearchPersonPromptDto;
use App\Application\Service\PersonPrompt\ListPersonsPrompts;
use App\Application\Service\PersonPrompt\ListPersonsPromptsService;
use App\Bridge\Laravel\Http\Controllers\Person\PersonAction;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowPersonPromptsListAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string $personId,
        SearchPersonPromptDto $search,
        ListPersonsPromptsService $service
    ): View {
        $prompts = $service->execute(new ListPersonsPrompts($search));

        /** @see /resources/views/persons/prompts.blade.php */
        return $this->view('persons.prompts', compact('personId', 'prompts'));
    }
}
