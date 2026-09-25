<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\AbstractDto;
use function array_key_exists;
use function is_string;
use function trim;

final class CupTableSearchDto extends AbstractDto
{
    public ?string $name = null;

    public static function requestValidationRules(): array
    {
        return ['name' => 'nullable|string|min:3|max:255'];
    }

    public static function parametersValidationRules(): array
    {
        return [
            'groupId' => [
                'required',
                'string',
                'regex:#\\A[MW]_(?:0|12|14|16|18|20|21|35|40|45|50|55|60|65|70|75|80)_[^/]*\\z#',
            ],
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

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        $this->setStringParam('name', $data);

        return $this;
    }
}
