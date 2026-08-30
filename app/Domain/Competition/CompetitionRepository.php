<?php

declare(strict_types=1);

namespace App\Domain\Competition;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;

interface CompetitionRepository
{
    public function add(Competition $competition): void;

    public function byId(int $id): ?Competition;

    /** @return Slice<Competition> */
    public function paginate(Criteria $criteria): Slice;

    public function lockById(int $id): ?Competition;

    public function update(Competition $competition): void;
}
