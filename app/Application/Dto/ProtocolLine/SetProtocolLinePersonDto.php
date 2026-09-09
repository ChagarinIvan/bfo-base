<?php

declare(strict_types=1);

namespace App\Application\Dto\ProtocolLine;

use App\Application\Dto\AbstractDto;

final class SetProtocolLinePersonDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return ['personId' => ['required', 'integer', 'exists:person,id']];
    }

    public function __construct(public readonly string $personId = '')
    {
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        return new self((string) ($data['personId'] ?? ''));
    }
}
