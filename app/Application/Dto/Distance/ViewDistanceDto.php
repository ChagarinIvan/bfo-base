<?php

declare(strict_types=1);

namespace App\Application\Dto\Distance;

final readonly class ViewDistanceDto
{
    public function __construct(
        public string $id,
        public string $eventId,
        public string $groupName,
        public int $length,
        public int $points,
        public bool $disqual,
    ) {
    }
}
