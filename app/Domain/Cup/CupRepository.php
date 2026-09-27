<?php

declare(strict_types=1);

namespace App\Domain\Cup;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Support\Collection;

interface CupRepository
{
    public function add(Cup $cup): void;

    public function lockById(int $id): ?Cup;

    public function byId(int $id): ?Cup;

    public function byCriteria(Criteria $criteria): Collection;

    /** @return Slice<Cup> */
    public function paginate(Criteria $criteria): Slice;

    public function update(Cup $cup): void;
}
