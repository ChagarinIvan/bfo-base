<?php

declare(strict_types=1);

namespace App\Domain\Event\Event;

use App\Domain\Event\EventProcessingStatus;
use App\Domain\Shared\AggregatedEvent;
use App\Domain\Event\Event;

final readonly class EventProcessingFailed extends AggregatedEvent
{
    public function __construct(
        public Event $event,
        public EventProcessingStatus $status,
    ) {
    }
}
