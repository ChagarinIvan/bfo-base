<?php

declare(strict_types=1);

namespace App\Application\Dto\Auth;

use App\Application\Dto\AbstractDto;

final class RegistrationDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return ['email' => ['required', 'email']];
    }
    public function __construct(public readonly string $email = '')
    {
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        return new self((string) ($data['email'] ?? ''));
    }
}
