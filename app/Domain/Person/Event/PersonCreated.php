<?php

declare(strict_types=1);

namespace App\Domain\Person\Event;

use App\Domain\Person\Person;
use App\Domain\Shared\AggregatedEvent;

final readonly class PersonCreated extends AggregatedEvent
{
    public function __construct(public Person $person)
    {
    }
}
