<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine\Event;

use App\Domain\Auth\Impression;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\AggregatedEvent;

final readonly class ProtocolLineRankActivated extends AggregatedEvent
{
    public ?int $personId;

    public function __construct(
        public ProtocolLine $protocolLine,
        public Impression $impression,
    ) {
        $this->personId = $protocolLine->person_id;
    }
}
