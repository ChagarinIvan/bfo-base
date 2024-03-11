<?php

declare(strict_types=1);

namespace App\Domain\PersonPayment;

use App\Models\PersonPayment;

final class PersonPaymentFactory
{
    public function create(PersonPaymentInput $input): PersonPayment
    {
        $personPayment = new PersonPayment();
        $personPayment->person_id = $input->personId;
        $personPayment->year = $input->year;
        $personPayment->date = $input->date;

        return $personPayment;
    }
}
