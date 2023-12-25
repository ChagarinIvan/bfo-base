<?php

declare(strict_types=1);

namespace App\Http\Controllers\Flags;

use App\Http\Controllers\AbstractAction;

abstract class AbstractFlagsAction extends AbstractAction
{
    protected function isFlagsRoute(): bool
    {
        return true;
    }
}
