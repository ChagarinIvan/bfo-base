<?php

declare(strict_types=1);

namespace App\Domain\Event;

enum EventProtocolStatus: string
{
    public function isTerminal(): bool
    {
        return $this === self::READY || $this === self::FAILED;
    }

    case QUEUED = 'queued';
    case PARSING = 'parsing';
    case IDENTIFYING = 'identifying';
    case REBUILDING_RANKS = 'rebuildingRanks';
    case READY = 'ready';
    case FAILED = 'failed';
}
