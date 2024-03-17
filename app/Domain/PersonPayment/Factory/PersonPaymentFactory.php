<?php

declare(strict_types=1);

namespace App\Domain\PersonPayment\Factory;

use App\Domain\PersonPayment\PersonPayment;

interface PersonPaymentFactory
{
    public function create(PersonPaymentInput $input): PersonPayment;
}
