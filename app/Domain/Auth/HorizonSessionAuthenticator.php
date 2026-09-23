<?php

declare(strict_types=1);

namespace App\Domain\Auth;

interface HorizonSessionAuthenticator
{
    public function start(int $userId): void;

    public function end(): void;
}
