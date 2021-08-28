<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractViewAction;

abstract class AbstractPersonViewAction extends AbstractViewAction
{
    protected function isPersonsRoute(): bool
    {
        return true;
    }
}
