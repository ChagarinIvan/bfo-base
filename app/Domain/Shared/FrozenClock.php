<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Carbon\Carbon;

final readonly class FrozenClock implements Clock
{
    private Carbon $now;

    public function __construct(Carbon $now = null)
    {
        $this->now = $now ?: Carbon::now();
    }

    public function now(): Carbon
    {
        return $this->now;
    }
}
