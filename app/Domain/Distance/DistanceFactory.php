<?php

declare(strict_types=1);

namespace App\Domain\Distance;

interface DistanceFactory
{
    public function create(DistanceInput $input): Distance;
}
