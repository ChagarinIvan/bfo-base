<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Application\Dto\Auth\UserId;

final readonly class EndHorizonSession
{
    public function __construct(private UserId $userId)
    {
    }

    public function userId(): int
    {
        return $this->userId->id;
    }
}
