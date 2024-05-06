<?php

declare(strict_types=1);

namespace App\Domain\CupEvent\Event;

use App\Domain\CupEvent\CupEvent;
use App\Domain\Shared\AggregatedEvent;

final readonly class CupEventUpdated extends AggregatedEvent
{
    public function __construct(public CupEvent $cupEvent)
    {
    }
}
