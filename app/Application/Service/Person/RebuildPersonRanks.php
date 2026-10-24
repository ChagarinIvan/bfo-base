<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Auth\UserId;
use App\Domain\Person\Audit\RankAuditTrigger;

final readonly class RebuildPersonRanks
{
    public function __construct(
        public int $personId,
        public UserId $userId,
        public RankAuditTrigger $trigger = RankAuditTrigger::Manual,
    ) {
    }
}
