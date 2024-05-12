<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent;

use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface CupEventRepository
{
    public function byId(int $id): ?CupEvent;

    public function lockById(int $id): ?CupEvent;

    public function byCriteria(Criteria $criteria): Collection;

    public function update(CupEvent $cupEvent): void;
}
