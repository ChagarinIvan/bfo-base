<?php

namespace App\Models;

/**
 * Class CupEventPoint
 */
class CupEventPoint
{
    public function __construct(
        public int $eventCupId,
        public int $protocolLineId,
        public int|string $points,
    ) {}

    public function equal(self $point): bool
    {
        return $this->eventCupId === $point->eventCupId &&
            $this->points === $point->points &&
            $this->protocolLineId === $point->protocolLineId;
    }
}
