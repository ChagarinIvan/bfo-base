<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Domain\Auth\Impression;

final readonly class RecordEventProtocolLineIdentification
{
    public function __construct(
        private int $eventProtocolId,
        private int $protocolLineId,
        private Impression $impression,
    ) {
    }

    public function eventProtocolId(): int
    {
        return $this->eventProtocolId;
    }

    public function protocolLineId(): int
    {
        return $this->protocolLineId;
    }

    public function impression(): Impression
    {
        return $this->impression;
    }
}
