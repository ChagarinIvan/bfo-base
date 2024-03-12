<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Carbon\Carbon;

final class ActualClock implements Clock
{
    public function now(): Carbon
    {
        return Carbon::now();
    }
}
