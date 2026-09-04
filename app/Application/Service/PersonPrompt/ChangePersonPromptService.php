<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Domain\Auth\Impression;
use App\Domain\PersonPrompt\Factory\PersonPromptFactory;
use App\Domain\PersonPrompt\Factory\PersonPromptInput;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;

final readonly class ChangePersonPromptService
{
    public function __construct(
        private PersonPromptRepository $prompts,
        private PersonPromptFactory $factory,
        private DeletePersonPromptService $deleter,
        private Clock $clock,
    ) {
    }

    public function execute(ChangePersonPrompt $command): void
    {
        $prompts = $this->prompts->byCriteria(new Criteria(['prompts' => [$command->prompt]]));

        if ($prompts->isEmpty()) {
            $this->prompts->add($this->factory->create(new PersonPromptInput(
                $command->prompt,
                $command->personId,
                $command->userId->id,
            )));

            return;
        }

        foreach ($prompts as $prompt) {
            $prompt->updateData(
                $prompt->prompt,
                $prompt->metaphone,
                new Impression($this->clock->now(), $command->userId->id),
                $command->personId,
            );
            $this->prompts->update($prompt);
        }
    }

    public function delete(string $prompt, int $userId): void
    {
        foreach ($this->prompts->byCriteria(new Criteria(['prompts' => [$prompt]])) as $personPrompt) {
            $this->deleter->execute(new DeletePersonPrompt((string) $personPrompt->id, new UserId($userId)));
        }
    }
}
