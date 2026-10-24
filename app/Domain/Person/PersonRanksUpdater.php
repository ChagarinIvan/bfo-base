<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Auth\Impression;
use App\Domain\Person\Audit\RankAuditTrigger;

final readonly class PersonRanksUpdater
{
    public function __construct(
        private PersonRepository $persons,
        private RankFactsCollector $factsCollector,
        private RankCalculator $calculator,
    )
    {
    }

    public function update(Person $person, Impression $impression, RankAuditTrigger $trigger): void
    {
        $state = $this->calculator->calculateFor($this->factsCollector->collect($person->id), $person->id, $person->birthday, $impression->at);
        $person->updateRanks($state, $impression, $trigger);
        $this->persons->update($person);
    }
}
