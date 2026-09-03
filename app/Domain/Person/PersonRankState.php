<?php

declare(strict_types=1);

namespace App\Domain\Person;

final readonly class PersonRankState
{
    /** @param list<PersonRankHistory> $history */
    public function __construct(
        public PersonRank $current,
        public array $history,
    ) {
    }
}
