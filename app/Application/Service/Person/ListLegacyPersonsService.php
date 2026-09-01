<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\LegacyViewPersonDto;
use App\Application\Dto\Person\PersonAssembler;
use App\Domain\Person\PersonRepository;
use function array_map;

final readonly class ListLegacyPersonsService
{
    public function __construct(
        private PersonRepository $persons,
        private PersonAssembler $assembler,
    ) {
    }

    /** @return LegacyViewPersonDto[] */
    public function execute(ListLegacyPersons $command): array
    {
        return array_map(
            $this->assembler->toLegacyViewPersonDto(...),
            $this->persons->byCriteria($command->criteria())->all()
        );
    }
}
