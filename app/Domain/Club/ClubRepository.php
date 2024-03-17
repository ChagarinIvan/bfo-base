<?php

declare(strict_types=1);

namespace App\Domain\Club;

use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface ClubRepository
{
    public function add(Club $club): void;

    public function byId(int $id): ?Club;

    public function lockById(int $id): ?Club;

    public function update(Club $club): void;

    public function byCriteria(Criteria $criteria): Collection;

    public function oneByCriteria(Criteria $criteria): ?Club;
}
