<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\Exception\PersonNotFound;
use App\Domain\Person\PersonRepository;
use App\Domain\PersonPrompt\Factory\PersonPromptFactory;
use App\Domain\PersonPrompt\PersonPromptRepository;

final readonly class AddPersonPromptService
{
    public function __construct(
        private PersonPromptFactory $factory,
        private PersonPromptRepository $prompts,
        private PersonPromptAssembler $assembler,
        private PersonRepository $persons,
    ) {
    }

    public function execute(AddPersonPrompt $command): ViewPersonPromptDto
    {
        $this->persons->byId($command->personId()) ?? throw new PersonNotFound();
        $prompt = $this->factory->create($command->input());
        $this->prompts->add($prompt);

        return $this->assembler->toViewPersonPromptDto($prompt);
    }
}
