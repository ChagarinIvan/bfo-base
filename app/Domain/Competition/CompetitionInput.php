<?php

declare(strict_types=1);

namespace App\Domain\Competition;

use Carbon\Carbon;

final readonly class CompetitionInput
{
    public function __construct(
        public string $name,
        public string $description,
        public Carbon $from,
        public Carbon $to,
        public int $userId,
    ) {
    }
}
