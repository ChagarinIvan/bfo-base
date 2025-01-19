<?php

declare(strict_types=1);

namespace App\Domain\Rank\Factory;

use App\Domain\Rank\Rank;

interface RankFactory
{
    public function create(RankInput $input): Rank;
}
