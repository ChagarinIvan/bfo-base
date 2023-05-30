<?php

namespace App\Services\Club;

use App\Models\Club;
use App\Services\ClubsService;

final class ClubFactory
{
    public function __construct(private readonly ClubsService $normalizer)
    {
    }

    public function create(string $name): Club
    {
        $club = new Club();
        $club->name = $name;
        $club->normalize_name = $this->normalizer::normalizeName($name);

        return $club;
    }
}
