<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Cup\CupCacheInvalidator;
use App\Domain\Distance\DistanceDeleter;
use App\Domain\Event\Exception\EventResultsCleanupError;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFactsCollector;
use App\Domain\ProtocolLine\ProtocolLineOperations;
use App\Domain\Shared\TransactionManager;
use Exception;

final readonly class StandardEventResultsCleaner implements EventResultsCleaner
{
    public function __construct(
        private ProtocolLineOperations $protocolLines,
        private DistanceDeleter $distances,
        private PersonRepository $persons,
        private RankFactsCollector $factsCollector,
        private RankCalculator $calculator,
        private CupCacheInvalidator $invalidator,
        private TransactionManager $transactional,
    ) {
    }

    public function clean(Event $event, Impression $impression): void
    {
        try {
            $this->cleanup($event, $impression);
        } catch (Exception $exception) {
            throw new EventResultsCleanupError(
                'Ошибка подготовки к замене протокола. Свяжитесь с администратором.',
                0,
                previous: $exception,
            );
        }
    }

    private function cleanup(Event $event, Impression $impression): void
    {
        // Roll back derived data before Event records a cleanup error.
        $this->transactional->run(function () use ($event, $impression): void {
            $personIds = $this->protocolLines->personIdsForEvent($event);
            $this->distances->deleteForEvent($event->id);
            $this->protocolLines->deleteEventLines($event);

            foreach ($personIds as $personId) {
                $person = $this->persons->lockById($personId);

                if ($person === null) {
                    continue;
                }

                $state = $this->calculator->calculate(
                    rankFacts: $this->factsCollector->collect($personId),
                    person: $person,
                    on: $impression->at->clone(),
                );
                $person->updateRanks($state, $impression);
                $this->persons->update($person);
            }

            if ($event->cups->isNotEmpty()) {
                $this->invalidator->invalidate();
            }
        });
    }
}
