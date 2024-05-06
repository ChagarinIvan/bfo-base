<?php

declare(strict_types=1);

namespace App\Domain\Cup;

use App\Domain\Competition\Competition;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface CupRepository
{
    public function add(Cup $cup): void;

    public function lockById(int $id): ?Cup;

    public function byId(int $id): ?Cup;

    public function byCriteria(Criteria $criteria): Collection;

    public function update(Cup $cup): void;
}
