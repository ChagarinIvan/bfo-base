<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Person\Exception\RanksUpdatingError;
use App\Domain\ProtocolLine\ProtocolLineOperations;

final readonly class StandardEventPersonRankUpdater implements EventPersonRankUpdater
{
    public function __construct(
        private ProtocolLineOperations $operations,
        private PersonRepository $persons,
        private RankFactsCollector $factsCollector,
        private RankCalculator $calculator,
    ) {
    }

    public function update(Event $event, Impression $impression): void
    {
        foreach ($this->operations->personIdsForEvent($event) as $personId) {
            $person = $this->persons->lockById($personId)
                ?? throw new RanksUpdatingError('Не удалось обновить разряды спортсменов.');

            $rankState = $this->calculator->calculate(
                rankFacts: $this->factsCollector->collect($personId),
                person: $person,
                on: $impression->at->clone(),
            );

            $person->updateRanks(
                $rankState,
                new Impression($impression->at->clone(), $impression->by),
            );

            $this->persons->update($person);
        }
    }
}
