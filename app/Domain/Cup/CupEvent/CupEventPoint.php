<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent;

use App\Domain\ProtocolLine\ProtocolLine;

final readonly class CupEventPoint
{
    public function __construct(
        public int $cupEventId,
        public ProtocolLine $protocolLine,
        public int|string|float $points,
    ) {
    }

    public function equal(self $point): bool
    {
        return $this->cupEventId === $point->cupEventId &&
            $this->points === $point->points &&
            $this->protocolLine->id === $point->protocolLine->id &&
            $this->protocolLine->person_id === $point->protocolLine->person_id
        ;
    }
}
