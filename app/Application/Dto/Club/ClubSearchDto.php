<?php

declare(strict_types=1);

namespace App\Application\Dto\Club;

use App\Application\Dto\AbstractDto;

final class ClubSearchDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'ids' => 'array',
            'ids.*' => 'numeric',
            'name' => 'string',
        ];
    }

    public function __construct(
        /** @var int[] */
        public array $ids = [],
        public ?string $name = null,
    ) {
    }

    public function fromArray(array $data): self
    {
        $this->ids = $data['ids'] ?? $this->ids;
        $this->setStringParam('name', $data);

        return $this;
    }
}
