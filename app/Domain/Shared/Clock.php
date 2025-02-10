<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use App\Models\Year;
use Carbon\Carbon;

interface Clock
{
    public function now(): Carbon;

    public function actualYear(): Year;

    /** @return Year[] */
    public function years(): array;
}
