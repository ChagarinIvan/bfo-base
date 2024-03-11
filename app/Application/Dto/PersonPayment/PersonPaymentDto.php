<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPayment;

final readonly class PersonPaymentDto
{
    public function __construct(
        public string $personId,
        public string $year,
        public string $date,
    ) {
    }
}
