<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\PersonAssembler;
use App\Application\Dto\Person\ViewPersonDto;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListPersonsService
{
    public function __construct(
        private PersonRepository $persons,
        private PersonAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewPersonDto> */
    public function execute(ListPersons $command): Slice
    {
        return $this->persons
            ->paginate($command->criteria())
            ->map($this->assembler->toViewPersonDto(...))
        ;
    }
}
