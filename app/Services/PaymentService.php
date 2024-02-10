<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PersonPayment;
use Carbon\Carbon;

class PaymentService
{
    public function addPayment(int $personId, Carbon $date, int $year = null): PersonPayment
    {
        $payment = PersonPayment::where('year', $year ?: $date->year)->wherePersonId($personId)->first();
        if ($payment === null) {
            $payment = new PersonPayment();
            $payment->person_id = $personId;
            $payment->year = $year;
            $payment->date = $date;
            $payment->save();
        }

        return $payment;
    }
}
