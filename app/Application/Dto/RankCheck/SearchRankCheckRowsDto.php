<?php

declare(strict_types=1);

namespace App\Application\Dto\RankCheck;

use App\Application\Dto\AbstractDto;
use function array_key_exists;
use function is_string;
use function trim;

final class SearchRankCheckRowsDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'name' => ['nullable', 'string', 'min:3', 'max:255'],
            'group' => ['nullable', 'string', 'min:3', 'max:255'],
            'hasPerson' => ['nullable', 'boolean'],
            'isEqual' => ['nullable', 'boolean'],
        ];
    }

    /** @param array<string, mixed> $data */
    public static function normaliseRequestData(array $data): array
    {
        foreach (['name', 'group'] as $field) {
            if (array_key_exists($field, $data) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);

                if ($data[$field] === '') {
                    unset($data[$field]);
                }
            }
        }

        return $data;
    }

    public function __construct(
        public ?string $name = null,
        public ?string $group = null,
        public ?bool $hasPerson = null,
        public ?bool $isEqual = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        if (array_key_exists('name', $data)) {
            $this->name = (string) $data['name'];
        }
        if (array_key_exists('group', $data)) {
            $this->group = (string) $data['group'];
        }
        if (array_key_exists('hasPerson', $data)) {
            $this->hasPerson = (bool) $data['hasPerson'];
        }
        if (array_key_exists('isEqual', $data)) {
            $this->isEqual = (bool) $data['isEqual'];
        }

        return $this;
    }
}
