<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPayment;

use App\Application\Dto\Auth\AuthAssembler;
use App\Models\PersonPayment;

final readonly class PersonPaymentAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewPersonPaymentDto(PersonPayment $payment): ViewPersonPaymentDto
    {
        return new ViewPersonPaymentDto(
            id: $payment->id,
            personId: $payment->person_id,
            year: $payment->year,
            date: $payment->date->format('Y-m-d'),
            created: $this->authAssembler->toImpressionDto($payment->created),
            updated: $this->authAssembler->toImpressionDto($payment->updated),
        );
    }
}
