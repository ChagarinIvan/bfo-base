<?php

declare(strict_types=1);

namespace App\Domain\Person;

use Carbon\Carbon;

final readonly class PersonRankState
{
    /** @param list<PersonRankHistory> $history */
    public function __construct(
        public PersonRank $current,
        public array $history,
        public ?Carbon $nextActivationOn = null,
    ) {
    }
}
