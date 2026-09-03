<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Criteria;

final readonly class RebuildExpiredPersonRanksService
{
    public function __construct(
        private PersonRepository $persons,
        private RebuildPersonRanksService $rebuild,
    ) {
    }

    public function execute(RebuildExpiredPersonRanks $command): int
    {
        $count = 0;
        foreach ($this->persons->idsByCriteria(new Criteria($command->criteria)) as $personId) {
            $this->rebuild->execute(new RebuildPersonRanks($personId, $command->userId));
            $count++;
        }

        return $count;
    }
}
