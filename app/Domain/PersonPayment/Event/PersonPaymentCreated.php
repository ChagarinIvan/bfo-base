<?php

declare(strict_types=1);

namespace App\Domain\PersonPayment\Event;

use App\Domain\PersonPayment\PersonPayment;
use App\Domain\Shared\AggregatedEvent;

final readonly class PersonPaymentCreated extends AggregatedEvent
{
    public function __construct(public PersonPayment $payment)
    {
    }
}
