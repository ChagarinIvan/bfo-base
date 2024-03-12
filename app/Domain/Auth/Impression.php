<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Shared\Footprint;
use Carbon\Carbon;

final readonly class Impression
{
    public function __construct(
        public Carbon $at,
        public int $by,
    ) {
    }
}
