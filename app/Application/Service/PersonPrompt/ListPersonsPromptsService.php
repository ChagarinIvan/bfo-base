<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListPersonsPromptsService
{
    public function __construct(
        private PersonPromptRepository $personsPrompts,
        private PersonPromptAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewPersonPromptDto> */
    public function paginate(ListPersonsPrompts $command): Slice
    {
        return $this->personsPrompts
            ->paginate($command->criteria())
            ->map($this->assembler->toViewPersonPromptDto(...))
        ;
    }
}
