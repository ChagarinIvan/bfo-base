<?php

declare(strict_types=1);

namespace App\Domain\RankCheck\Factory;

use App\Domain\RankCheck\RankCheck;

interface RankCheckFactory
{
    public function create(RankCheckInput $input): RankCheck;
}
