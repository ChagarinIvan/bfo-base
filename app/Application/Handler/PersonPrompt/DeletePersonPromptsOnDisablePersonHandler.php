<?php

declare(strict_types=1);

namespace App\Application\Handler\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\PersonPrompt\DeletePersonPrompt;
use App\Application\Service\PersonPrompt\DeletePersonPromptService;
use App\Domain\Person\Event\PersonDisabled;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class DeletePersonPromptsOnDisablePersonHandler implements ShouldQueue
{
    public function __construct(private PersonPromptRepository $prompts, private DeletePersonPromptService $service)
    {
    }

    public function handle(PersonDisabled $event): void
    {
        $prompts = $this->prompts->byCriteria(new Criteria([
            'personId' => $event->person->id,
            'activePerson' => false,
        ]));

        foreach ($prompts as $prompt) {
            $this->service->execute(new DeletePersonPrompt((string) $prompt->id, new UserId($event->person->updated->by)));
        }
    }
}
