<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\LegacyViewPersonDto;
use App\Application\Dto\Person\PersonAssembler;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class UpdatePersonInfoService
{
    public function __construct(
        private PersonRepository $persons,
        private Clock $clock,
        private PersonAssembler $assembler,
        private TransactionManager $transactional,
    ) {
    }

    /** @throws PersonNotFound */
    public function execute(UpdatePersonInfo $command): LegacyViewPersonDto
    {
        return $this->transactional->run(function () use ($command): LegacyViewPersonDto {
            $person = $this->persons->lockById($command->id()) ?? throw new PersonNotFound();
            $impression = new Impression($this->clock->now(), $command->userId());
            $person->updateInfo($command->info(), $impression);
            $this->persons->update($person);

            return $this->assembler->toLegacyViewPersonDto($person);
        });
    }
}
