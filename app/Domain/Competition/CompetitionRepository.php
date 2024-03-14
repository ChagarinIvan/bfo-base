<?php

declare(strict_types=1);

namespace App\Domain\Competition;

use App\Models\Competition;

interface CompetitionRepository
{
    public function add(Competition $competition): void;

    public function byId(int $id): ?Competition;

    public function lockById(int $id): ?Competition;

    public function update(Competition $competition): void;
}
