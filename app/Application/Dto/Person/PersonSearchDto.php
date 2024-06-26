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
            'withoutLines' => '',
        ];
    }

    public function __construct(
        public ?string $clubId = null,
        public ?string $year = null,
        public bool $withoutLines = false,
    ) {
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('clubId', $data);
        $this->setStringParam('year', $data);
        $this->withoutLines = (bool) ($data['withoutLines'] ?? false);

        return $this;
    }
}
