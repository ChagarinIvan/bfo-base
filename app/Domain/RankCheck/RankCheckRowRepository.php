<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;

interface RankCheckRowRepository
{
    public function add(RankCheckRow $row): void;

    /** @return Slice<RankCheckRow> */
    public function paginate(Criteria $criteria): Slice;
}
