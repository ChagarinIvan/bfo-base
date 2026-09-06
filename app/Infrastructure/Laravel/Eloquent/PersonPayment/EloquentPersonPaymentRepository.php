<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\PersonPayment;

use App\Domain\PersonPayment\PersonPayment;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentPersonPaymentRepository implements PersonPaymentRepository
{
    public function add(PersonPayment $personPayment): void
    {
        $personPayment->create();
    }

    public function update(PersonPayment $personPayment): void
    {
        $personPayment->save();
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->buildQuery($criteria)->get();
    }

    /** @return Slice<PersonPayment> */
    public function paginate(Criteria $criteria): Slice
    {
        return new Slice(new EloquentQueryAdapter($this->buildQuery($criteria)));
    }

    public function lockOneByCriteria(Criteria $criteria): ?PersonPayment
    {
        /** @var PersonPayment|null $personPayment */
        $personPayment = $this
            ->buildQuery($criteria)
            ->lockForUpdate()
            ->first()
        ;

        return $personPayment;
    }

    /** @return Builder<PersonPayment> */
    private function buildQuery(Criteria $criteria): Builder
    {
        $query = PersonPayment::select('persons_payments.*')
            ->join('person', 'person.id', '=', 'persons_payments.person_id')
            ->where('person.active', true)
            ->where('persons_payments.person_id', $criteria->param('personId'))
        ;

        if ($criteria->hasParam('year')) {
            $query->where('persons_payments.year', $criteria->param('year'));
        }

        return $query->orderBy('persons_payments.id', 'desc');
    }
}
