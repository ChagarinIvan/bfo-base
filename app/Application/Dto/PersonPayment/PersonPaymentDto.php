<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPayment;

use App\Application\Dto\AbstractDto;
use function substr;

final class PersonPaymentDto extends AbstractDto
{
    public string $personId;
    public string $date;

    public static function requestValidationRules(): array
    {
        return [
            'personId' => 'required|numeric',
            'date' => 'required|date_format:Y-m-d',
        ];
    }

    public function year(): int
    {
        return (int) substr($this->date, 0, 4);
    }

    public function fromArray(array $data): self
    {
        $this->personId = $data['personId'];
        $this->date = $data['date'];

        return $this;
    }
}
