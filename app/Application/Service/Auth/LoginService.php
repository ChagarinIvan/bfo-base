<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Application\Dto\Auth\LoginAssembler;
use App\Application\Dto\Auth\ViewTokenDto;
use App\Application\Exception\AuthenticationFailed;
use App\Domain\Auth\Exception\InvalidCredentials;
use App\Domain\Auth\LoginAuthenticator;

final readonly class LoginService
{
    public function __construct(private LoginAuthenticator $authenticator)
    {
    }

    /** @throws AuthenticationFailed */
    public function execute(Login $command): ViewTokenDto
    {
        try {
            $token = $this->authenticator->authenticate($command->credentials);
        } catch (InvalidCredentials $exception) {
            throw new AuthenticationFailed(previous: $exception);
        }

        return LoginAssembler::toViewTokenDto($token);
    }
}
