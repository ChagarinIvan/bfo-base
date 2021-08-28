<?php

declare(strict_types=1);

namespace App\Http\Controllers\Flags;

use App\Http\Controllers\AbstractViewAction;

abstract class AbstractFlagsViewAction extends AbstractViewAction
{
    protected function isFlagsRoute(): bool
    {
        return true;
    }
}
