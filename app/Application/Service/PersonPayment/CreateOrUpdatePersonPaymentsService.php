<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPayment;

use App\Domain\PersonPayment\PersonPaymentFactory;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class CreateOrUpdatePersonPaymentsService
{
    public function __construct(
        private PersonPaymentRepository $payments,
        private PersonPaymentFactory $factory,
        private TransactionManager $transaction,
        private Clock $clock,
    ) {
    }

    public function execute(CreateOrUpdatePersonPayments $command): void
    {
        $this->transaction->run(
            function () use ($command): void {
                $personPayment = $this->payments->lockOneByCriteria($command->criteria());

                if ($personPayment === null) {
                    $personPayment = $this->factory->create($command->input());
                    $this->payments->add($personPayment);
                } elseif (!$personPayment->date->isSameDay($command->date())) {
                    $personPayment->updateDate($command->date(), $command->impression($this->clock));
                    $this->payments->update($personPayment);
                }
            }
        );
    }
}
