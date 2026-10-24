<?php

declare(strict_types=1);

namespace App\Application\Dto\Person\RankAudit;

final readonly class AppliedRankDto
{
    public function __construct(
        public int $rankId,
        public ?string $startedOn,
        public ?string $activatedOn,
        public ?string $finishedOn,
        public ?string $nextActivationOn,
    )
    {
    }
}
