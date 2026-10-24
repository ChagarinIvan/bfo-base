<?php

declare(strict_types=1);

namespace App\Domain\Person\Audit;

use App\Domain\Auth\Impression;
use App\Domain\Person\PersonRank;
use Carbon\Carbon;

final readonly class RankAuditAction
{
    public function __construct(
        public int $personId,
        public RankAuditTrigger $trigger,
        public Impression $created,
        public PersonRank $current,
        public ?Carbon $nextActivationOn,
        public ?int $id = null,
    )
    {
    }
}
