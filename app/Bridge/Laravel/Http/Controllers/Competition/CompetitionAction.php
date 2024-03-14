<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use App\Bridge\Laravel\Http\Controllers\Action;

trait CompetitionAction
{
    use Action;

    protected function isCompetitionsRoute(): bool
    {
        return true;
    }
}
