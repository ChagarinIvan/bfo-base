<?php

declare(strict_types=1);

namespace App\Domain\Competition;

use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface CompetitionRepository
{
    public function add(Competition $competition): void;

    public function byId(int $id): ?Competition;

    public function byCriteria(Criteria $criteria): Collection;

    public function lockById(int $id): ?Competition;

    public function update(Competition $competition): void;
}
