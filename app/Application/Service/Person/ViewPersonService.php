<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\LegacyViewPersonDto;
use App\Application\Dto\Person\PersonAssembler;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Domain\Person\PersonRepository;

final readonly class ViewPersonService
{
    public function __construct(
        private PersonRepository $persons,
        private PersonAssembler $assembler,
    ) {
    }

    /** @throws PersonNotFound */
    public function execute(ViewPerson $command): LegacyViewPersonDto
    {
        $person = $this->persons->byId($command->id(), $command->resources()) ?? throw new PersonNotFound();

        return $this->assembler->toLegacyViewPersonDto($person, $command->resources());
    }
}
