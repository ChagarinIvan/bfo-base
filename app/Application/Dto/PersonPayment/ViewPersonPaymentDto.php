<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPayment;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Serialization\Groups;

final readonly class ViewPersonPaymentDto
{
    public function __construct(
        public string $id,
        public string $personId,
        public string $year,
        public string $date,
        #[Groups(['authenticated'])]
        public ImpressionDto $created,
        #[Groups(['authenticated'])]
        public ImpressionDto $updated,
    ) {
    }
}
