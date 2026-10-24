<?php

declare(strict_types=1);

namespace App\Domain\Person\Audit;

enum RankAuditTrigger: string
{
    case Manual = 'manual';
    case Queued = 'queued';
    case Scheduled = 'scheduled';
    case Refill = 'refill';
    case ProtocolImport = 'protocol_import';
    case ProtocolCleanup = 'protocol_cleanup';
    case Activation = 'activation';
    case Reassignment = 'reassignment';
    case Correction = 'correction';
}
