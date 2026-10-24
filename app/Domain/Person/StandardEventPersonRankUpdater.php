<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\Person\Exception\RanksUpdatingError;
use App\Domain\ProtocolLine\ProtocolLineOperations;
use App\Domain\Shared\TransactionManager;

final readonly class StandardEventPersonRankUpdater implements EventPersonRankUpdater
{
    public function __construct(
        private ProtocolLineOperations $operations,
        private PersonRepository $persons,
        private PersonRanksUpdater $updater,
        private TransactionManager $transactional,
    ) {
    }

    public function update(Event $event, Impression $impression): void
    {
        foreach ($this->operations->personIdsForEvent($event) as $personId) {
            $this->transactional->run(function () use ($personId, $impression): void {
                $person = $this->persons->lockById($personId) ?? throw new RanksUpdatingError('Не удалось обновить разряды спортсменов.');
                $this->updater->update($person, $impression, RankAuditTrigger::ProtocolImport);
            });
        }
    }
}
