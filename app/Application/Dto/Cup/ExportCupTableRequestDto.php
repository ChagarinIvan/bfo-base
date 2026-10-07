<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\AbstractDto;

final class ExportCupTableRequestDto extends AbstractDto
{
    public string $format = 'csv';

    public static function requestValidationRules(): array
    {
        return ['format' => 'sometimes|required|string|in:csv,html'];
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        $this->setStringParam('format', $data);

        return $this;
    }

    public function isHtml(): bool
    {
        return $this->format === 'html';
    }
}
