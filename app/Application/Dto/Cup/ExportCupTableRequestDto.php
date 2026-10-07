<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\AbstractDto;

final class ExportCupTableRequestDto extends AbstractDto
{
    public string $format = 'csv';
    public ?string $groupId = null;

    public static function requestValidationRules(): array
    {
        return [
            'format' => 'sometimes|required|string|in:csv,html',
            'groupId' => [
                'sometimes',
                'required',
                'string',
                'regex:#\A[MW]_(?:0|12|14|16|18|20|21|35|40|45|50|55|60|65|70|75|80)_[^/]*\z#',
            ],
        ];
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        $this->setStringParam('format', $data);
        $this->setStringParam('groupId', $data);

        return $this;
    }

    public function isHtml(): bool
    {
        return $this->format === 'html';
    }
}
