<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface RankCheckRepository
{
    public function byId(int $id): ?RankCheck;

    public function lockById(int $id): ?RankCheck;

    /** @return Collection<int, RankCheck> */
    public function byCriteria(Criteria $criteria): Collection;

    public function add(RankCheck $check): void;

    public function delete(RankCheck $check): void;

    public function update(RankCheck $check): void;
}
