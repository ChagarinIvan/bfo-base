<?php

declare(strict_types=1);

namespace App\Domain\Competition\Factory;

use App\Domain\Competition\CompetitionInfo;

final readonly class CompetitionInput
{
    public function __construct(
        public CompetitionInfo $info,
        public int $userId,
    ) {
    }
}
