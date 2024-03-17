<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Club;

use App\Bridge\Laravel\Http\Controllers\Action;

trait ClubAction
{
    use Action;

    protected function isClubsRoute(): bool
    {
        return true;
    }
}
