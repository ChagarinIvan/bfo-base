<?php

declare(strict_types=1);

namespace App\Application\Dto\Auth;

use App\Application\Dto\AbstractDto;

final class LoginDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
    public function __construct(public readonly string $email = '', public readonly string $password = '')
    {
    }

    public function fromArray(array $data): self
    {
        return new self((string) ($data['email'] ?? ''), (string) ($data['password'] ?? ''));
    }
}
