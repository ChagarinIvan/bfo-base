<?php

declare(strict_types=1);

namespace App\Domain\Event\Event;

use App\Domain\Auth\Impression;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Shared\AggregatedEvent;

final readonly class EventProcessingFailed extends AggregatedEvent
{
    public function __construct(
        public int $eventId,
        public string $processingToken,
        public EventProcessingStatus $status,
        public Impression $impression,
    ) {
    }
}
