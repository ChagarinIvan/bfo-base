<?php

declare(strict_types=1);

namespace Tests\Application\Service\Auth;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Auth\Logout;
use App\Application\Service\Auth\LogoutService;
use App\Domain\Auth\CurrentTokenRevoker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogoutServiceTest extends TestCase
{
    #[Test]
    public function it_revokes_current_token(): void
    {
        $revoker = $this->createMock(CurrentTokenRevoker::class);
        $revoker
            ->expects($this->once())
            ->method('revoke')
            ->with(42)
        ;

        new LogoutService($revoker)->execute(new Logout(new UserId(42)));
    }
}
