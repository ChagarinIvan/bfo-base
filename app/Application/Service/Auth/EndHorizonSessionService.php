<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Domain\Auth\HorizonSessionAuthenticator;

final readonly class EndHorizonSessionService
{
    public function __construct(private HorizonSessionAuthenticator $authenticator)
    {
    }

    public function execute(EndHorizonSession $command): void
    {
        $this->authenticator->end();
    }
}
