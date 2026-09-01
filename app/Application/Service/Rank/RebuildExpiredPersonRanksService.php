<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;

final readonly class RebuildExpiredPersonRanksService
{
    public function __construct(
        private PersonRepository $persons,
        private RebuildPersonRanksService $rebuild,
    ) {
    }

    public function execute(): void
    {
        foreach ($this->persons->byCriteria(new Criteria(['rankFinishedBefore' => Carbon::today()])) as $person) {
            $this->rebuild->execute(new RebuildPersonRanks($person->id));
        }
    }
}
