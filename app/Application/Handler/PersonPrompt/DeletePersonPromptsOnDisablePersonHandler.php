<?php

declare(strict_types=1);

namespace App\Application\Handler\PersonPrompt;

use App\Application\Dto\PersonPrompt\SearchPersonPromptDto;
use App\Application\Service\PersonPrompt\DeletePersonPrompt;
use App\Application\Service\PersonPrompt\DeletePersonPromptService;
use App\Application\Service\PersonPrompt\ListPersonsPrompts;
use App\Application\Service\PersonPrompt\ListPersonsPromptsService;
use App\Domain\Person\Event\PersonDisabled;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class DeletePersonPromptsOnDisablePersonHandler implements ShouldQueue
{
    public function __construct(private ListPersonsPromptsService $prompts, private DeletePersonPromptService $service)
    {
    }

    public function handle(PersonDisabled $event): void
    {
        $prompts = $this->prompts->execute(new ListPersonsPrompts(new SearchPersonPromptDto((string) $event->person->id, false)));

        foreach ($prompts as $prompt) {
            $this->service->execute(new DeletePersonPrompt($prompt->id));
        }
    }
}
