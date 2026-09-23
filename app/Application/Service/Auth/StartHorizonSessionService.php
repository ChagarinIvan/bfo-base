<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Application\Service\Auth\Exception\HorizonAccessDenied;
use App\Domain\Auth\HorizonAccessAuthorizer;
use App\Domain\Auth\HorizonSessionAuthenticator;

final readonly class StartHorizonSessionService
{
    public function __construct(
        private HorizonAccessAuthorizer $authorizer,
        private HorizonSessionAuthenticator $authenticator,
    )
    {
    }

    public function execute(StartHorizonSession $command): void
    {
        if (!$this->authorizer->canAccess($command->userId())) {
            throw new HorizonAccessDenied('Horizon access is not allowed.');
        }

        $this->authenticator->start($command->userId());
    }
}
