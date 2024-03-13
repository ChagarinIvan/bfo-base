<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPayment;

use App\Application\Dto\Auth\ImpressionDto;

final readonly class ViewPersonPaymentDto
{
    public function __construct(
        public string $id,
        public string $personId,
        public string $year,
        public string $date,
        public ImpressionDto $created,
        public ImpressionDto $updated,
    ) {
    }
}
