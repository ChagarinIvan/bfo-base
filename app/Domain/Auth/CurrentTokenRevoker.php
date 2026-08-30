<?php

declare(strict_types=1);

namespace App\Domain\Auth;

interface CurrentTokenRevoker
{
    public function revoke(int $userId): void;
}
