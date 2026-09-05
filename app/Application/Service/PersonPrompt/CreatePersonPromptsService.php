<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Domain\Auth\Impression;
use App\Domain\Person\PersonRepository;
use App\Domain\PersonPrompt\Factory\PersonPromptFactory;
use App\Domain\PersonPrompt\Factory\PersonPromptInput;
use App\Domain\PersonPrompt\PersonPromptGenerator;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;
use function array_diff;

final readonly class CreatePersonPromptsService
{
    public function __construct(
        private PersonRepository       $persons,
        private PersonPromptRepository $prompts,
        private PersonPromptGenerator  $generator,
        private PersonPromptFactory    $factory,
        private Clock                  $clock,
    ) {
    }

    public function execute(CreatePersonPrompts $command): void
    {
        $hasNamesake = $this->persons->byCriteria(new Criteria([
            'firstname' => $command->firstname(),
            'lastname' => $command->lastname(),
        ]))->count() > 1;

        $generatedPromptLines = $this->generator->generate(
            $command->firstname(),
            $command->lastname(),
            $command->birthdayYear(),
            $hasNamesake,
        );

        $existingPromptLines = $this->prompts
            ->byCriteria(new Criteria(['personId' => $command->personId()]))
            ->map(static fn ($prompt): string => $prompt->prompt)
            ->all()
        ;

        foreach (array_diff($generatedPromptLines, $existingPromptLines) as $prompt) {
            $this->prompts->add($this->factory->create(new PersonPromptInput(
                $prompt,
                $command->personId(),
                $command->userId(),
            )));
        }

        if ($hasNamesake) {
            foreach ($this->generator->generate($command->firstname(), $command->lastname(), null, false) as $prompt) {
                foreach ($this->prompts->byCriteria(new Criteria(['prompts' => [$prompt]])) as $personPrompt) {
                    $personPrompt->disable(new Impression($this->clock->now(), $command->userId()));
                    $this->prompts->update($personPrompt);
                }
            }
        }
    }
}
