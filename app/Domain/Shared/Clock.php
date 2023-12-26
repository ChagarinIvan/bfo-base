<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Carbon\Carbon;

interface Clock
{
    public function now(): Carbon;
}
