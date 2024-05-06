<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Bridge\Laravel\Http\Controllers\Action;

trait CupAction
{
    use Action;

    protected function isCupsRoute(): bool
    {
        return true;
    }
}
