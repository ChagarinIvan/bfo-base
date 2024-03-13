<?php

declare(strict_types=1);

namespace App\Domain\Competition;

use Carbon\Carbon;

final readonly class CompetitionInfo
{
    public function __construct(
        public string $name,
        public string $description,
        public Carbon $from,
        public Carbon $to,
    ) {
    }
}
