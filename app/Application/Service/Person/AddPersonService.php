<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\LegacyViewPersonDto;
use App\Application\Dto\Person\PersonAssembler;
use App\Application\Service\Person\Exception\FailedToAddPerson;
use App\Domain\Person\Exception\PersonInfoAlreadyExist;
use App\Domain\Person\Factory\PersonFactory;
use App\Domain\Person\PersonRepository;

final readonly class AddPersonService
{
    public function __construct(
        private PersonFactory $factory,
        private PersonRepository $persons,
        private PersonAssembler $assembler,
    ) {
    }

    /** @throws FailedToAddPerson */
    public function execute(AddPerson $command): LegacyViewPersonDto
    {
        try {
            $person = $this->factory->create($command->personInput());
        } catch (PersonInfoAlreadyExist $e) {
            throw FailedToAddPerson::personAlreadyExist($e);
        }

        $this->persons->add($person);

        return $this->assembler->toLegacyViewPersonDto($person);
    }
}
