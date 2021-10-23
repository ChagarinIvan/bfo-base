<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Club;
use Illuminate\Support\Collection;

class ClubsService
{
    /**
     * @return Collection|Club[]
     */
    public function getAllClubs(): Collection
    {
        return Club::all();
    }
}
