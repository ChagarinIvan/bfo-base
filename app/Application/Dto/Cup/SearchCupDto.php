<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\AbstractDto;
use App\Models\Year;
use Illuminate\Validation\Rules\Enum;
use function array_key_exists;
use function array_map;
use function is_string;
use function trim;

final class SearchCupDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'year' => ['nullable', new Enum(Year::class)],
            'name' => ['nullable', 'string', 'min:3', 'max:255'],
            'visible' => ['nullable', 'boolean'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'min:1'],
        ];
    }

    /** @param array<string, mixed> $data */
    public static function normaliseRequestData(array $data): array
    {
        if (array_key_exists('name', $data) && is_string($data['name'])) {
            $data['name'] = trim($data['name']);

            if ($data['name'] === '') {
                unset($data['name']);
            }
        }

        return $data;
    }

    public function __construct(
        /** @var list<string>|null */
        public ?array $ids = null,
        public ?string $year = null,
        public ?string $name = null,
        public ?bool $visible = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        return new self(
            ids: isset($data['ids']) ? array_map(static fn (int|string $id): string => (string) $id, $data['ids']) : null,
            year: isset($data['year']) ? (string) $data['year'] : null,
            name: isset($data['name']) ? (string) $data['name'] : null,
            visible: array_key_exists('visible', $data)
                ? (bool) (int) $data['visible']
                : null,
        );
    }
}
