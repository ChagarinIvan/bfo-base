<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Bridge\Laravel\Http\Controllers\Action;

trait RankAction
{
    use Action;

    protected function isRanksRoute(): bool
    {
        return true;
    }
}
