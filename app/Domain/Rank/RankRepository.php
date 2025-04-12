<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface RankRepository
{
    public function add(Rank $rank): void;

    public function byId(int $id): ?Rank;

    public function byCriteria(Criteria $criteria): Collection;

    public function oneByCriteria(Criteria $criteria): ?Rank;

    public function deleteByCriteria(Criteria $criteria): void;
}
