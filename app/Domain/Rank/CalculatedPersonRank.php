<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use Carbon\CarbonImmutable;

final readonly class CalculatedPersonRank
{
    /** @param list<PersonRankHistory> $history */
    public function __construct(
        public Rank $currentRank,
        public ?CarbonImmutable $startedOn,
        public ?CarbonImmutable $activatedOn,
        public ?CarbonImmutable $finishedOn,
        public array $history,
    ) {
    }
}
