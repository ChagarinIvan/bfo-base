<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Domain\PersonPrompt\PersonPromptRepository;

final readonly class ViewPersonPromptService
{
    public function __construct(
        private PersonPromptRepository $personsPrompts,
        private PersonPromptAssembler $assembler,
    ) {
    }

    /** @throws PersonPromptNotFound */
    public function execute(ViewPersonPrompt $command): ViewPersonPromptDto
    {
        $personPrompt = $this->personsPrompts->byId($command->id()) ?? throw new PersonPromptNotFound();

        return $this->assembler->toViewPersonPromptDto($personPrompt);
    }
}
