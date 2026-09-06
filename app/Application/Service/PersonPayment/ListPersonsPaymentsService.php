<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPayment;

use App\Application\Dto\PersonPayment\PersonPaymentAssembler;
use App\Application\Dto\PersonPayment\ViewPersonPaymentDto;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListPersonsPaymentsService
{
    public function __construct(
        private PersonPaymentRepository $payments,
        private PersonPaymentAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewPersonPaymentDto> */
    public function paginate(ListPersonsPayments $command): Slice
    {
        return $this->payments
            ->paginate($command->criteria())
            ->map($this->assembler->toViewPersonPaymentDto(...))
        ;
    }
}
