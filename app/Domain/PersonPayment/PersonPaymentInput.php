<?php

declare(strict_types=1);

namespace App\Domain\PersonPayment;

use Carbon\Carbon;

final readonly class PersonPaymentInput
{
    public function __construct(
        public int $personId,
        public int $year,
        public Carbon $date,
        public int $userId,
    ) {
    }
}
