<?php

declare(strict_types=1);

namespace App\Application\Dto\CupEvent;

use App\Application\Dto\AbstractDto;
use function array_key_exists;
use function is_string;
use function trim;

final class CupEventPointSearchDto extends AbstractDto
{
    public string $groupId;

    public ?string $name = null;

    public static function requestValidationRules(): array
    {
        return [
            'groupId' => 'required|string|min:1|max:255',
            'name' => 'nullable|string|min:3|max:255',
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
        $this->setStringParam('groupId', $data);
        $this->setStringParam('name', $data);

        return $this;
    }
}
