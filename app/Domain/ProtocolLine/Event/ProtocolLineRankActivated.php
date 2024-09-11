<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine\Event;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\AggregatedEvent;

final readonly class ProtocolLineRankActivated extends AggregatedEvent
{
    public function __construct(public ProtocolLine $protocolLine)
    {
    }
}
