<?php

namespace App\Domain\Club\Repository;

use App\Domain\Club\Club;

interface ClubRepository
{
    public function byId(int $id): ?Club;
}
