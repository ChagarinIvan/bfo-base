<?php

declare(strict_types=1);

namespace App\Domain\Person\Audit;

enum RankAuditTrigger: string
{
    case Manual = 'manual';
    case Scheduled = 'scheduled';
    case ProtocolImport = 'protocol_import';
    case ProtocolCleanup = 'protocol_cleanup';
    case Activation = 'activation';
    case Reassignment = 'reassignment';
}
