<?php

declare(strict_types=1);

namespace App\Domain\PersonPayment;

use App\Domain\Auth\Impression;
use App\Domain\PersonPayment\Factory\PersonPaymentFactory;
use App\Domain\PersonPayment\Factory\PersonPaymentInput;
use App\Domain\Shared\Clock;

final readonly class StandardPersonPaymentFactory implements PersonPaymentFactory
{
    public function __construct(private Clock $clock)
    {
    }

    public function create(PersonPaymentInput $input): PersonPayment
    {
        $personPayment = new PersonPayment();
        $personPayment->person_id = $input->personId;
        $personPayment->year = $input->year;
        $personPayment->date = $input->date;
        $personPayment->created = $personPayment->updated = new Impression($this->clock->now(), $input->userId);

        return $personPayment;
    }
}
