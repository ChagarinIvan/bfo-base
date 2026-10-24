<?php

declare(strict_types=1);

namespace App\Application\Dto\Person\RankAudit;

use App\Application\Dto\Auth\ImpressionDto;
use App\Domain\Person\Audit\RankAuditTrigger;

final readonly class RankAuditDto
{
    public function __construct(
        public string $id,
        public int $personId,
        public RankAuditTrigger $trigger,
        public ImpressionDto $created,
        public AppliedRankDto $applied,
    )
    {
    }
}
