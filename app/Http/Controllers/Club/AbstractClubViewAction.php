<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Controllers\AbstractViewAction;

abstract class AbstractClubViewAction extends AbstractViewAction
{
    protected function isClubsRoute(): bool
    {
        return true;
    }
}
