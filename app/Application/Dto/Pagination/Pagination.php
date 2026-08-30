<?php

declare(strict_types=1);

namespace App\Application\Dto\Pagination;

use App\Application\Dto\AbstractDto;

final class Pagination extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'page' => ['integer', 'min:1'],
            'perPage' => ['integer', 'min:1', 'max:100'],
        ];
    }
    public function __construct(
        public int $page = 1,
        public int $perPage = 20,
    )
    {
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        return new self(
            page: (int) ($data['page'] ?? $this->page),
            perPage: (int) ($data['perPage'] ?? $this->perPage),
        );
    }
}
