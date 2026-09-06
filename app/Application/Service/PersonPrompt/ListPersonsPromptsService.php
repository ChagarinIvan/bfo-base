<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\Exception\PersonNotFound;
use App\Domain\Person\PersonRepository;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListPersonsPromptsService
{
    public function __construct(
        private PersonPromptRepository $personsPrompts,
        private PersonPromptAssembler $assembler,
        private PersonRepository $persons,
    ) {
    }

    /** @return Slice<ViewPersonPromptDto> */
    public function paginate(ListPersonsPrompts $command): Slice
    {
        $criteria = $command->criteria();

        if ($criteria->hasParam('personId')) {
            $this->persons->byId((int) $criteria->param('personId')) ?? throw new PersonNotFound();
        }

        return $this->personsPrompts
            ->paginate($criteria)
            ->map($this->assembler->toViewPersonPromptDto(...))
        ;
    }
}
