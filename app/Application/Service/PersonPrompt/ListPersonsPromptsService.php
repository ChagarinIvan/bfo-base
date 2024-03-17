<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Domain\PersonPrompt\PersonPromptRepository;
use function array_map;

final readonly class ListPersonsPromptsService
{
    public function __construct(
        private PersonPromptRepository $personsPrompts,
        private PersonPromptAssembler $assembler,
    ) {
    }

    /**
     * @return ViewPersonPromptDto[]
     */
    public function execute(ListPersonsPrompts $command): array
    {
        return array_map(
            $this->assembler->toViewPersonPromptDto(...),
            $this->personsPrompts->byCriteria($command->criteria())->all(),
        );
    }
}
