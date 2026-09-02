<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Rank\Rank;
use Carbon\Carbon;

final readonly class PersonRank
{
    public function __construct(
        public Rank $rank,
        public ?Carbon $startedOn,
        public ?Carbon $activatedOn,
        public ?Carbon $finishedOn,
    ) {
    }
}
