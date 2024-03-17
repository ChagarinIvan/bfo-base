<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\ProtocolLine\ProtocolLine;

class CupEventPoint
{
    public function __construct(
        public readonly int $eventCupId,
        public readonly ProtocolLine $protocolLine,
        public readonly int|string|float $points,
    ) {
    }

    public function equal(self $point): bool
    {
        return $this->eventCupId === $point->eventCupId &&
            $this->points === $point->points &&
            $this->protocolLine->id === $point->protocolLine->id &&
            $this->protocolLine->person_id === $point->protocolLine->person_id;
    }
}
