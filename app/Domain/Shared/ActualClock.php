<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use App\Models\Year;
use Carbon\Carbon;

final class ActualClock implements Clock
{
    public function now(): Carbon
    {
        return Carbon::now();
    }

    public function actualYear(): Year
    {
        return Year::actualYear();
    }

    /** @return Year[] */
    public function years(): array
    {
        return Year::cases();
    }
}
