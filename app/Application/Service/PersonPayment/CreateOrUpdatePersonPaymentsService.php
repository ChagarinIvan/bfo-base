<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPayment;

use App\Application\Dto\PersonPayment\PersonPaymentAssembler;
use App\Application\Dto\PersonPayment\ViewPersonPaymentDto;
use App\Application\Service\PersonPayment\Exception\PersonNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Person\PersonRepository;
use App\Domain\PersonPayment\Factory\PersonPaymentFactory;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class CreateOrUpdatePersonPaymentsService
{
    public function __construct(
        private PersonPaymentRepository $payments,
        private PersonPaymentFactory $factory,
        private PersonPaymentAssembler $assembler,
        private PersonRepository $persons,
        private TransactionManager $transaction,
        private Clock $clock,
    ) {
    }

    public function execute(CreateOrUpdatePersonPayments $command): ViewPersonPaymentDto
    {
        $this->persons->byId($command->personId()) ?? throw new PersonNotFound();

        $personPayment = $this->transaction->run(
            function () use ($command): PersonPayment {
                $personPayment = $this->payments->lockOneByCriteria($command->criteria());

                if ($personPayment === null) {
                    $personPayment = $this->factory->create($command->input());
                    $this->payments->add($personPayment);
                } elseif (!$personPayment->sameDate($command->date())) {
                    $personPayment->updateDate(
                        $command->date(),
                        new Impression($this->clock->now(), $command->userId()),
                    );
                    $this->payments->update($personPayment);
                }

                return $personPayment;
            }
        );

        return $this->assembler->toViewPersonPaymentDto($personPayment);
    }
}
