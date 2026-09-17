<?php

declare(strict_types=1);

namespace App\Domain\RankCheck\Factory;

use App\Domain\RankCheck\RankCheckRow;

interface RankCheckRowFactory
{
    public function create(RankCheckRowInput $input): RankCheckRow;
}
