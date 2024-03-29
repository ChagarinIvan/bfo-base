<?php

declare(strict_types=1);

namespace App\Domain\PersonPayment;

use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface PersonPaymentRepository
{
    public function add(PersonPayment $personPayment): void;

    public function byCriteria(Criteria $criteria): Collection;

    public function lockOneByCriteria(Criteria $criteria): ?PersonPayment;

    public function update(PersonPayment $personPayment): void;
}
