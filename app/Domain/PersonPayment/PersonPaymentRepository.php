<?php

declare(strict_types=1);

namespace App\Domain\PersonPayment;

use App\Domain\Shared\Criteria;
use App\Models\PersonPayment;
use Illuminate\Support\Collection;

interface PersonPaymentRepository
{
    public function add(PersonPayment $personPayment): PersonPayment;

    public function byCriteria(Criteria $criteria): Collection;
}
