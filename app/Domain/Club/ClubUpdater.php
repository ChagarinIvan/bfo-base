<?php

declare(strict_types=1);

namespace App\Domain\Club;

interface ClubUpdater
{
    public function update(Club $club, ClubInput $input): Club;
}
