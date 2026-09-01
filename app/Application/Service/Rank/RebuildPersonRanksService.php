<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Domain\Person\PersonRepository;
use App\Domain\Rank\RankCalculator;
use App\Domain\Rank\RankFacts;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;
use Carbon\CarbonImmutable;

final readonly class RebuildPersonRanksService
{
    public function __construct(
        private PersonRepository $persons,
        private RankFacts $facts,
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

            $projection = $this->calculator->calculate(
                $this->facts->forPerson($command->personId),
                CarbonImmutable::instance($this->clock->now()),
            );

            $person->replaceRankProjection($projection);
            $this->persons->saveRankProjection($person, $projection);
        });
    }
}
