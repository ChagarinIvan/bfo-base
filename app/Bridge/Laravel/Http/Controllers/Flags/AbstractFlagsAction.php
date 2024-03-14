<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Flags;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;

abstract class AbstractFlagsAction extends AbstractAction
{
    protected function isFlagsRoute(): bool
    {
        return true;
    }
}
