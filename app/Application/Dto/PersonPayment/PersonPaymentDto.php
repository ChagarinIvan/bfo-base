<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPayment;

use App\Application\Dto\AbstractDto;

final class PersonPaymentDto extends AbstractDto
{
    public readonly string $year;

    public function __construct(
        public readonly string $personId,
        public readonly string $date,
    ) {
        $this->year = substr($this->date, 0, 4);
    }

    public static function requestValidationRules(): array
    {
        return [
            'personId' => 'required|numeric',
            'date' => 'required|date_format:Y-m-d',
        ];
    }

    public function fromArray(array $data): self
    {
        return new self(
            personId: $data['personId'],
            date: $data['date'],
        );
    }
}
