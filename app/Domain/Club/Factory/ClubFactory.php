<?php

declare(strict_types=1);

namespace App\Domain\Club\Factory;

use App\Domain\Club\Club;
use App\Domain\Club\Exception\ClubAlreadyExist;

interface ClubFactory
{
    /** @throws ClubAlreadyExist */
    public function create(ClubInput $input): Club;
}
