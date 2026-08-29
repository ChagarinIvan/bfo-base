<?php

declare(strict_types=1);

namespace Tests\Application\Service\Auth;

use App\Application\Dto\Auth\LoginDto;
use App\Application\Dto\Auth\ViewTokenDto;
use App\Application\Exception\AuthenticationFailed;
use App\Application\Service\Auth\Login;
use App\Application\Service\Auth\LoginService;
use App\Domain\Auth\AccessToken;
use App\Domain\Auth\Exception\InvalidCredentials;
use App\Domain\Auth\LoginAuthenticator;
use App\Domain\Auth\LoginCredentials;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LoginServiceTest extends TestCase
{
    #[Test]
    public function it_authenticates_user_and_returns_token_dto(): void
    {
        $authenticator = $this->createMock(LoginAuthenticator::class);
        $response = new AccessToken('1|token');
        $login = new LoginDto('user@example.com', 'secret');

        $authenticator
            ->expects($this->once())
            ->method('authenticate')
            ->with(new LoginCredentials('user@example.com', 'secret'))
            ->willReturn($response)
        ;

        $result = new LoginService($authenticator)->execute(new Login($login));

        $this->assertEquals(new ViewTokenDto('1|token', 'Bearer'), $result);
    }

    #[Test]
    public function it_propagates_domain_invalid_credentials_error(): void
    {
        $authenticator = $this->createStub(LoginAuthenticator::class);

        $authenticator
            ->method('authenticate')
            ->willThrowException(new InvalidCredentials())
        ;

        $this->expectException(AuthenticationFailed::class);

        new LoginService($authenticator)->execute(new Login(new LoginDto('user@example.com', 'wrong')));
    }
}
