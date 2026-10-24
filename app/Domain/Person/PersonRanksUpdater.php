<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Auth\Impression;
use App\Domain\Person\Audit\RankAuditEntry;
use App\Domain\Person\Audit\RankAuditRepository;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\Person\Audit\RankRebuildFailure;
use App\Domain\Person\Audit\RankSnapshot;
use Carbon\Carbon;
use Throwable;

final readonly class PersonRanksUpdater
{
    public function __construct(
        private PersonRepository $persons,
        private RankFactsCollector $factsCollector,
        private RankCalculator $calculator,
        private RankAuditRepository $audit,
    ) {
    }

    public function update(Person $person, Impression $impression, RankAuditTrigger $trigger, ?Carbon $on = null): void
    {
        $before = null;
        try {
            $before = $this->audit->snapshot($person->id);
            $state = $this->calculator->calculateFor($this->factsCollector->collect($person->id), $person->id, $person->birthday, $on ?? $impression->at);
            $after = RankSnapshot::fromState($state);
            if ($before->equals($after)) {
                return;
            }
            $person->updateRanks($state, $impression);
            $this->persons->update($person);
            $this->audit->add(new RankAuditEntry($person->id, $trigger, $impression, $before, $after));
        } catch (Throwable $error) {
            throw new RankRebuildFailure(new RankAuditEntry($person->id, $trigger, $impression, $before, null), $error);
        }
    }

    public function recordFailure(RankRebuildFailure $error): void
    {
        $this->audit->add($error->entry);
    }
}
