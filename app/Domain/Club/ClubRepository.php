<?php

declare(strict_types=1);

namespace App\Domain\Club;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Support\Collection;

interface ClubRepository
{
    public function add(Club $club): void;

    public function byId(int $id): ?Club;

    public function lockById(int $id): ?Club;

    public function update(Club $club): void;

    public function oneByNormalizedName(string $normalizedName): ?Club;

    public function byCriteria(Criteria $criteria): Collection;

    public function oneByCriteria(Criteria $criteria): ?Club;

    /** @return Slice<Club> */
    public function paginate(Criteria $criteria): Slice;
}
