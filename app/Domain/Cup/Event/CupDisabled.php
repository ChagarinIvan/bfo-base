<?php

declare(strict_types=1);

namespace App\Domain\Cup\Event;

use App\Domain\Cup\Cup;
use App\Domain\Shared\AggregatedEvent;

final readonly class CupDisabled extends AggregatedEvent
{
    public function __construct(public Cup $competition)
    {
    }
}
