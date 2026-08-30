<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Application\Dto\Auth\UserId;

final class Logout
{
    public int $id {
        get => $this->userId->id;
    }

    public function __construct(private readonly UserId $userId)
    {
    }
}
