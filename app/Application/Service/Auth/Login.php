<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Application\Dto\Auth\LoginDto;
use App\Domain\Auth\LoginCredentials;

final class Login
{
    public LoginCredentials $credentials {
        get => new LoginCredentials($this->dto->email, $this->dto->password);
    }

    public function __construct(private readonly LoginDto $dto)
    {
    }
}
