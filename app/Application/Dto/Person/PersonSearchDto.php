<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\AbstractDto;

final class PersonSearchDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'clubId' => 'numeric',
            'year' => 'numeric',
            'withoutLinesAndPayments' => '',
        ];
    }

    public function __construct(
        public ?string $clubId = null,
        public ?string $year = null,
        public bool $withoutLinesAndPayments = false,
    ) {
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('clubId', $data);
        $this->setStringParam('year', $data);
        $this->withoutLinesAndPayments = (bool) ($data['withoutLinesAndPayments'] ?? false);

        return $this;
    }
}
