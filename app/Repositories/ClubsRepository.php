<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Club\Club;

class ClubsRepository
{
    public function findByNormalizeName(string $normalizeName): ?Club
    {
        return Club::whereNormalizeName($normalizeName)->first();
    }
}
