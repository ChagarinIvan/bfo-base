<?php

declare(strict_types=1);

namespace App\Domain\Event\Event;

use App\Domain\Auth\Impression;
use App\Domain\Shared\AggregatedEvent;

final readonly class EventProtocolCleaned extends AggregatedEvent
{
    public function __construct(public int $eventId, public string $processingToken, public Impression $impression)
    {
    }
}
