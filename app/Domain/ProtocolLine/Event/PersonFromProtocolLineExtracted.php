<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine\Event;

use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\AggregatedEvent;

final readonly class PersonFromProtocolLineExtracted extends AggregatedEvent
{
    public function __construct(
        public ProtocolLine $protocolLine,
        public Person $person,
        public Impression $impression,
    ) {
    }
}
