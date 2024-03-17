<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPayment;

use App\Application\Dto\PersonPayment\PersonPaymentAssembler;
use App\Application\Dto\PersonPayment\ViewPersonPaymentDto;
use App\Domain\PersonPayment\PersonPaymentRepository;
use function array_map;

final readonly class ListPersonsPaymentsService
{
    public function __construct(
        private PersonPaymentRepository $payments,
        private PersonPaymentAssembler $assembler,
    ) {
    }

    /**
     * @return ViewPersonPaymentDto[]
     */
    public function execute(ListPersonsPayments $command): array
    {
        return array_map(
            $this->assembler->toViewPersonPaymentDto(...),
            $this->payments->byCriteria($command->criteria())->all(),
        );
    }
}
