<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Service\Person\Exception\PersonNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Person\PersonRanksUpdater;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class RebuildPersonRanksService
{
    public function __construct(
        private PersonRepository $persons,
        private PersonRanksUpdater $updater,
        private Clock $clock,
        private TransactionManager $transactional,
    ) {
    }

    /** @throws PersonNotFound */
    public function execute(RebuildPersonRanks $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $person = $this->persons->lockById($command->personId) ?? throw new PersonNotFound();
            $this->updater->update($person, new Impression($this->clock->now(), $command->userId->id), $command->trigger);
        });
    }
}
