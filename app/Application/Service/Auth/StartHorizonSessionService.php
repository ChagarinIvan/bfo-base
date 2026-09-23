<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Domain\Auth\HorizonSessionAuthenticator;

final readonly class StartHorizonSessionService
{
    public function __construct(private HorizonSessionAuthenticator $authenticator)
    {
    }

    public function execute(StartHorizonSession $command): void
    {
        $this->authenticator->start($command->userId());
    }
}
