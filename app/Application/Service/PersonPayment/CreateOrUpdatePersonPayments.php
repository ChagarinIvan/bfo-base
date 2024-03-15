<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPayment;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPayment\PersonPaymentDto;
use App\Domain\Auth\Impression;
use App\Domain\PersonPayment\Factory\PersonPaymentInput;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;

final readonly class CreateOrUpdatePersonPayments
{
    public function __construct(
        private PersonPaymentDto $dto,
        private UserId $userId,
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
            userId: $this->userId->id,
        );
    }

    public function date(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->dto->date);
    }

    public function impression(Clock $clock): Impression
    {
        return new Impression($clock->now(), $this->userId->id);
    }
}
