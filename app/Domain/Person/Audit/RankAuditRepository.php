<?php

declare(strict_types=1);

namespace App\Domain\Person\Audit;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;

interface RankAuditRepository
{
    /** @return Slice<RankAuditAction> */
    public function byCriteria(Criteria $criteria): Slice;
}
