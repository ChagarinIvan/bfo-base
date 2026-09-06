<?php

declare(strict_types=1);

namespace App\Domain\PersonPayment;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Support\Collection;

interface PersonPaymentRepository
{
    public function add(PersonPayment $personPayment): void;

    public function byCriteria(Criteria $criteria): Collection;

    /** @return Slice<PersonPayment> */
    public function paginate(Criteria $criteria): Slice;

    public function lockOneByCriteria(Criteria $criteria): ?PersonPayment;

    public function update(PersonPayment $personPayment): void;
}
