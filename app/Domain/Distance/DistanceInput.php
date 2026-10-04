<?php

declare(strict_types=1);

namespace App\Domain\Distance;

final readonly class DistanceInput
{
    public function __construct(
        public int $eventId,
        public int $groupId,
        public int $length,
        public int $points,
    ) {
    }
}
