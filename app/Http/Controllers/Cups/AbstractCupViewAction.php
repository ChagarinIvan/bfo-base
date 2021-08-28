<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Http\Controllers\AbstractViewAction;

abstract class AbstractCupViewAction extends AbstractViewAction
{
    protected function isCupsRoute(): bool
    {
        return true;
    }
}
