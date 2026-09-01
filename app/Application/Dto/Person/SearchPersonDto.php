<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\AbstractDto;
use function array_key_exists;

final class SearchPersonDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'clubId' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function __construct(public ?int $clubId = null)
    {
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        if (array_key_exists('clubId', $data)) {
            $this->clubId = (int) $data['clubId'];
        }

        return $this;
    }
}
