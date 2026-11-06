<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Auth\UserId;
use App\Domain\Person\RankCalculationReason;
final readonly class RebuildExpiredPersonRanks
{
    /** @param array<string, mixed> $criteria */
    public function __construct(
        public UserId $userId,
        public array $criteria = [],
        public RankCalculationReason $reason = RankCalculationReason::Daily,
    ) {
    }
}
