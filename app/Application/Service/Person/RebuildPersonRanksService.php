<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Domain\Auth\Impression;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class RebuildPersonRanksService
{
    public function __construct(
        private PersonRepository $persons,
        private RankFactsCollector $factsCollector,
        private RankCalculator $calculator,
        private Clock $clock,
        private TransactionManager $transactional,
    ) {
    }

    public function execute(RebuildPersonRanks $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $person = $this->persons->lockById($command->personId);

            if ($person === null) {
                return;
            }

            $now = $this->clock->now();
            $rankFacts = $this->factsCollector->collect($command->personId);

            $rankState = $this->calculator->calculate(
                rankFacts: $rankFacts,
                person: $person,
                on: $now->clone(),
            );

            $person->updateRanks(
                $rankState,
                new Impression($now->clone(), $command->userId->id),
            );

            $this->persons->update($person);
        });
    }
}
