<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\PersonPayment;

use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Criteria;
use App\Models\PersonPayment;
use Illuminate\Support\Collection;

final class EloquentPersonPaymentRepository implements PersonPaymentRepository
{
    public function byCriteria(Criteria $criteria): Collection
    {
        return PersonPayment::where('person_id', $criteria->param('personId'))->get();
    }
}
