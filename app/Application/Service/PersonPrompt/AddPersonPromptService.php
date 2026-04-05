<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Domain\PersonPrompt\Factory\PersonPromptFactory;
use App\Domain\PersonPrompt\PersonPromptRepository;

final readonly class AddPersonPromptService
{
    public function __construct(
        private PersonPromptFactory $factory,
        private PersonPromptRepository $prompts,
        private PersonPromptAssembler $assembler,
    ) {
    }

    public function execute(AddPersonPrompt $command): ViewPersonPromptDto
    {
        $prompt = $this->factory->create($command->input());
        $this->prompts->add($prompt);

        return $this->assembler->toViewPersonPromptDto($prompt);
    }
}
