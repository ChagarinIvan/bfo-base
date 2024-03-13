<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPayment;

final readonly class SearchPersonPaymentsDto
{
    public function __construct(
        public string $personId,
    ) {
    }
}
