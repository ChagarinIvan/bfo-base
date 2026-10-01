<?php

declare(strict_types=1);

namespace App\Domain\Event;
use function in_array;

enum EventProcessingStatus: string
{
    public function isTerminal(): bool
    {
        return in_array($this, [self::READY, self::PARSING_ERROR, self::IDENTIFYING_ERROR, self::REBUILDING_RANKS_ERROR], true);
    }
    case PARSING = 'parsing';
    case IDENTIFYING = 'identifying';
    case REBUILDING_RANKS = 'rebuildingRanks';
    case READY = 'ready';
    case PARSING_ERROR = 'parsingError';
    case IDENTIFYING_ERROR = 'identifyingError';
    case REBUILDING_RANKS_ERROR = 'rebuildingRanksError';
}
