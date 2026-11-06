<?php

declare(strict_types=1);

namespace App\Domain\Person;

enum RankCalculationReason: string
{
    case Unknown = 'unknown';
    case Manual = 'manual';
    // Retained for persisted metadata and previously queued jobs.
    case Protocol = 'protocol';
    case EventResultsProcessed = 'event_results_processed';
    case EventResultsCleared = 'event_results_cleared';
    case ProtocolPersonChanged = 'protocol_person_changed';
    case Daily = 'daily';
    case Refill = 'refill';
}
