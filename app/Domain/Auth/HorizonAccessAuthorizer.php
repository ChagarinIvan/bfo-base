<?php

declare(strict_types=1);

namespace App\Domain\Auth;

interface HorizonAccessAuthorizer
{
    public function canAccess(int $userId): bool;
}
