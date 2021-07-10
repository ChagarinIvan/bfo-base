<?php

namespace App\Models;

/**
 * Class CupEventPoint
 */
class CupEventPoint
{
    public function __construct(
        public int $eventCupId,
        public ProtocolLine $protocolLine,
        public int $points,
    ) {}

    public function equal(self $point): bool
    {
        return $this->eventCupId === $point->eventCupId &&
            $this->points === $point->points &&
            $this->protocolLine->id === $point->protocolLine->id &&
            $this->protocolLine->person_id === $point->protocolLine->person_id;
    }
}
