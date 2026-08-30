<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Domain\Auth\CurrentTokenRevoker;

final readonly class LogoutService
{
    public function __construct(private CurrentTokenRevoker $tokenRevoker)
    {
    }

    public function execute(Logout $command): void
    {
        $this->tokenRevoker->revoke($command->id);
    }
}
