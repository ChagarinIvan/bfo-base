<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPayment;

use App\Application\Dto\AbstractDto;

final class SearchPersonPaymentsDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'personId' => 'required|numeric',
            'year' => 'nullable|numeric|digits:4',
        ];
    }

    public function __construct(
        public ?string $personId = null,
        public ?string $year = null,
    ) {
    }

    public function fromArray(array $data): self
    {
        $this->setStringParam('personId', $data);
        $this->setStringParam('year', $data);

        return $this;
    }
}
