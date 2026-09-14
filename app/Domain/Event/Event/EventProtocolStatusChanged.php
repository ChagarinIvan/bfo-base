<?php

declare(strict_types=1);

namespace App\Domain\Event\Event;

use App\Domain\Event\EventProtocol;
use App\Domain\Shared\AggregatedEvent;

final readonly class EventProtocolStatusChanged extends AggregatedEvent
{
    public function __construct(public EventProtocol $eventProtocol)
    {
    }
}
