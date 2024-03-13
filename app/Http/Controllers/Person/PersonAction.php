<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

trait PersonAction
{
    protected function isPersonsRoute(): bool
    {
        return true;
    }
}
