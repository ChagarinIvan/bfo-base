<?php

declare(strict_types=1);

namespace App\Domain\Event\Event;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Shared\AggregatedEvent;

final readonly class EventProtocolActivated extends AggregatedEvent
{
    public function __construct(
        public Event $event,
        public int $protocolId,
        public Impression $impression,
    ) {
    }
}
