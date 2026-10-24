<?php

declare(strict_types=1);

namespace App\Domain\Person\Audit;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;

interface RankAuditRepository
{
    public function snapshot(int $personId): RankSnapshot;

    public function add(RankAuditEntry $entry): void;

    /** @return Slice<RankAuditEntry> */
    public function byCriteria(Criteria $criteria): Slice;
}
