<?php

declare(strict_types=1);

namespace App\Infrastructure\Sanctum;

use App\Domain\Auth\CurrentTokenRevoker;
use Illuminate\Http\Request;

final readonly class SanctumCurrentTokenRevoker implements CurrentTokenRevoker
{
    public function __construct(private Request $request)
    {
    }

    public function revoke(int $userId): void
    {
        $this->request->user()?->currentAccessToken()?->delete();
    }
}
