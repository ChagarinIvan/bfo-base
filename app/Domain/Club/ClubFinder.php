<?php

declare(strict_types=1);

namespace App\Domain\Club;

interface ClubFinder
{
    public function findByName(string $clubName): ?Club;
}
