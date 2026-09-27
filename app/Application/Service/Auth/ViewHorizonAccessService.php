<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Application\Dto\Auth\HorizonAccessDto;
use App\Domain\Auth\HorizonAccessAuthorizer;

final readonly class ViewHorizonAccessService
{
    public function __construct(private HorizonAccessAuthorizer $authorizer)
    {
    }

    public function execute(ViewHorizonAccess $command): HorizonAccessDto
    {
        return new HorizonAccessDto($this->authorizer->canAccess($command->userId()));
    }
}
