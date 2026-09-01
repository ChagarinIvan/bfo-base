<?php

declare(strict_types=1);

namespace App\Domain\Club;

final readonly class ClubInput
{
    public function __construct(
        public ClubInfo $info,
        public int $userId,
    ) {
    }
}
