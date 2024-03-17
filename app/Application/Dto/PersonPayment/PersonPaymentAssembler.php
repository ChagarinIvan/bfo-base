<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPayment;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\PersonPayment\PersonPayment;

final readonly class PersonPaymentAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewPersonPaymentDto(PersonPayment $personPayment): ViewPersonPaymentDto
    {
        return new ViewPersonPaymentDto(
            id: (string) $personPayment->id,
            personId: (string) $personPayment->person_id,
            year: (string)$personPayment->year,
            date: $personPayment->date->format('Y-m-d'),
            created: $this->authAssembler->toImpressionDto($personPayment->created),
            updated: $this->authAssembler->toImpressionDto($personPayment->updated),
        );
    }
}
