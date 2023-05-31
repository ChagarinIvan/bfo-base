<?php

namespace App\Repositories;

use App\Models\Club;

class ClubsRepository
{
    public function findByNormalizeName(string $normalizeName): ?Club
    {
        return Club::whereNormalizeName($normalizeName)->first();
    }

    public function add(Club $club): void
    {
        $club->save();
    }
}
