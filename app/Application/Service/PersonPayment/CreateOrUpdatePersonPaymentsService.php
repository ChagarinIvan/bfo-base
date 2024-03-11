<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPayment;

use App\Domain\PersonPayment\PersonPaymentFactory;
use App\Domain\PersonPayment\PersonPaymentRepository;

final readonly class CreateOrUpdatePersonPaymentsService
{
    public function __construct(
        private PersonPaymentRepository $payments,
        private PersonPaymentFactory $factory,
    ) {
    }

    public function execute(CreateOrUpdatePersonPayments $command): void
    {
        $personPayment = $this->payments->byCriteria($command->criteria())->first();

        if ($personPayment === null) {
            $personPayment = $this->factory->create($command->input());
        }

        $personPayment->date = $command->date();
        $this->payments->add($personPayment);
    }
}
