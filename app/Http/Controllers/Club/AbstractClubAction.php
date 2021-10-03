<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Controllers\AbstractAction;

abstract class AbstractClubAction extends AbstractAction
{
    protected function isClubsRoute(): bool
    {
        return true;
    }
}
