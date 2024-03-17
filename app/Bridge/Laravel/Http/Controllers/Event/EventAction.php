<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Bridge\Laravel\Http\Controllers\Action;

trait EventAction
{
    use Action;

    protected function isCompetitionsRoute(): bool
    {
        return true;
    }
}
