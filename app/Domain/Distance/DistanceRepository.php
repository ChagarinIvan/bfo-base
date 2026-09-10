<?php

declare(strict_types=1);

namespace App\Domain\Distance;

use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface DistanceRepository
{
    /** @return Collection<int, Distance> */
    public function byCriteria(Criteria $criteria): Collection;

    public function oneByCriteria(Criteria $criteria): ?Distance;
}
