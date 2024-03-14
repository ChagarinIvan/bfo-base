<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Bridge\Laravel\Http\Controllers\Action;

trait PersonAction
{
    use Action;

    protected function isPersonsRoute(): bool
    {
        return true;
    }
}
