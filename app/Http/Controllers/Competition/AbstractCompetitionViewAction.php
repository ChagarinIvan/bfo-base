<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\AbstractViewAction;

abstract class AbstractCompetitionViewAction extends AbstractViewAction
{
    protected function isCompetitionsRoute(): bool
    {
        return true;
    }
}
