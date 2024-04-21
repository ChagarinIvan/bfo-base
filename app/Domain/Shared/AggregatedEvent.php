<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract readonly class AggregatedEvent
{
    use Dispatchable, SerializesModels;
}
