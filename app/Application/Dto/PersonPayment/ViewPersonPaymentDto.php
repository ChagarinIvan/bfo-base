<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPayment;

final readonly class ViewPersonPaymentDto
{
    public function __construct(
        public int $id,
        public int $personId,
        public int $year,
        public string $date,
    ) {
    }
}
