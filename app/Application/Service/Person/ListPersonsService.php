<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\PersonAssembler;
use App\Application\Dto\Person\ViewPersonDto;
use App\Domain\Person\PersonRepository;
use function array_map;

final readonly class ListPersonsService
{
    public function __construct(
        private PersonRepository $persons,
        private PersonAssembler $assembler,
    ) {
    }

    /** @return ViewPersonDto[] */
    public function execute(ListPersons $command): array
    {
        return array_map(
            $this->assembler->toViewPersonDto(...),
            $this->persons->byCriteria($command->criteria())->all()
        );
    }
}
