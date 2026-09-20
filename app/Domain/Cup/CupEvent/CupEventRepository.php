<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Support\Collection;

interface CupEventRepository
{
    public function byId(int $id): ?CupEvent;

    public function lockById(int $id): ?CupEvent;

    public function byCriteria(Criteria $criteria): Collection;

    /** @return Slice<CupEvent> */
    public function paginate(Criteria $criteria): Slice;

    public function update(CupEvent $cupEvent): void;
}
