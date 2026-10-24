<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\RankAudit\RankAuditAssembler;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Domain\Person\Audit\RankAuditRepository;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\Person\Audit\RankRebuildFailure;
use App\Domain\Person\Audit\RankSnapshot;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class CorrectPersonRanksService
{
    public function __construct(
        private PersonRepository $persons,
        private RankFactsCollector $facts,
        private RankCalculator $calculator,
        private RankAuditRepository $audit,
        private RebuildPersonRanksService $rebuild,
        private TransactionManager $transaction,
        private Clock $clock,
        private RankAuditAssembler $assembler,
    ) {
    }

    /** @return iterable<array<string, mixed>> */
    public function execute(CorrectPersonRanks $command): iterable
    {
        if ($command->personId !== null && $this->persons->byId($command->personId) === null) {
            throw new PersonNotFound();
        }
        foreach ($this->persons->idsByCriteria($command->criteria()) as $id) {
            try {
                $result = $this->transaction->run(function () use ($command, $id): ?array {
                    $person = $this->persons->lockById($id);
                    if ($person === null) {
                        return null;
                    }
                    $before = $this->audit->snapshot($id);
                    $facts = $this->facts->collect($id);
                    $now = $this->clock->now();
                    $after = RankSnapshot::fromState($this->calculator->calculateFor($facts, $person->id, $person->birthday, $now));
                    if ($before->equals($after)) {
                        return null;
                    }
                    if ($command->apply) {
                        $this->rebuild->execute(new RebuildPersonRanks($id, $command->userId, RankAuditTrigger::Correction, $now));
                    }

                    return ['personId' => $id, 'before' => $this->assembler->snapshot($before), 'after' => $this->assembler->snapshot($after), 'applied' => $command->apply];
                });
            } catch (RankRebuildFailure $error) {
                $this->audit->add($error->entry);
                throw $error;
            }
            if ($result !== null) {
                yield $result;
            }
        }
    }
}
