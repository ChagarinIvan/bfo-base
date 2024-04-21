<?php

declare(strict_types=1);

namespace App\Domain\Event\Event;

use App\Domain\Event\Event;
use App\Domain\Shared\AggregatedEvent;

final readonly class EventCreated extends AggregatedEvent
{
    public function __construct(public Event $event)
    {
    }
}
