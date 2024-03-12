<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\PersonPayment;

use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Criteria;
use App\Models\PersonPayment;
use Illuminate\Support\Collection;

final class EloquentPersonPaymentRepository implements PersonPaymentRepository
{
    public function add(PersonPayment $personPayment): void
    {
        $personPayment->save();
    }

    public function update(PersonPayment $personPayment): void
    {
        $personPayment->save();
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        $query = PersonPayment::where('person_id', $criteria->param('personId'));

        if ($criteria->hasParam('year')) {
            $query->where('year', $criteria->param('year'));
        }

        return $query->get();
    }
}
