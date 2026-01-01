<?php

declare(strict_types=1);

namespace App\Domain\Event\Event;

use App\Domain\Event\Event;
use App\Domain\Shared\AggregatedEvent;

final readonly class EventUpdated extends AggregatedEvent
{
    public function __construct(public Event $event, public bool $withProtocolUpdate)
    {
    }
}
