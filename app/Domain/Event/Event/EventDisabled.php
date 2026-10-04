<?php

declare(strict_types=1);

namespace App\Domain\Event\Event;

use App\Domain\Event\Event;
use App\Domain\Shared\AggregatedEvent;

final readonly class EventDisabled extends AggregatedEvent
{
    public int $eventId;

    public string $processingToken;

    public function __construct(public Event $event)
    {
        $this->eventId = $event->id;
        $this->processingToken = $event->processing_token;
    }
}
