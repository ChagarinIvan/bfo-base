<?php

declare(strict_types=1);

namespace App\Domain\Cup\Event;

use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\Shared\AggregatedEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class CupCreated extends AggregatedEvent
{
    public function __construct(public Cup $competition)
    {
    }
}
