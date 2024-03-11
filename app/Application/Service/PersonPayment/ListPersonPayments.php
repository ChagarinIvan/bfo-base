<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPayment;

use App\Application\Dto\PersonPayment\SearchPersonPaymentsDto;
use App\Domain\Shared\Criteria;
use function get_object_vars;

final readonly class ListPersonPayments
{
    public function __construct(
        private SearchPersonPaymentsDto $search,
    ) {
    }

    public function criteria(): Criteria
    {
        return new Criteria(get_object_vars($this->search));
    }
}
