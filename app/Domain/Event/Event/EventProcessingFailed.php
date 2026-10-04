<?php

declare(strict_types=1);

namespace App\Domain\Event\Event;

use App\Domain\Event\Event;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Shared\AggregatedEvent;

final readonly class EventProcessingFailed extends AggregatedEvent
{
    public int $eventId;

    public string $processingToken;

    public function __construct(
        public Event $event,
        public EventProcessingStatus $status,
    ) {
        $this->eventId = $event->id;
        $this->processingToken = $event->processing_token;
    }
}
