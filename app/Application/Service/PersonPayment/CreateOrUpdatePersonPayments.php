<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPayment;

use App\Application\Dto\PersonPayment\PersonPaymentDto;
use App\Domain\PersonPayment\PersonPaymentInput;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;

final readonly class CreateOrUpdatePersonPayments
{
    public function __construct(
        private PersonPaymentDto $dto,
    ) {
    }

    public function criteria(): Criteria
    {
        return new Criteria([
            'personId' => $this->dto->personId,
            'year' => $this->dto->year,
        ]);
    }

    public function input(): PersonPaymentInput
    {
        return new PersonPaymentInput(
            personId: (int) $this->dto->personId,
            year: (int) $this->dto->year,
            date: $this->date(),
        );
    }

    public function date(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->dto->date);
    }
}
