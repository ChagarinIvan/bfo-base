<?php

declare(strict_types=1);

namespace App\Infrastructure\Sanctum;

use App\Domain\Auth\AccessToken;
use App\Domain\Auth\Exception\InvalidCredentials;
use App\Domain\Auth\LoginAuthenticator;
use App\Domain\Auth\LoginCredentials;
use Illuminate\Contracts\Hashing\Hasher;

final readonly class SanctumLoginAuthenticator implements LoginAuthenticator
{
    public function __construct(private Hasher $hasher)
    {
    }

    public function authenticate(LoginCredentials $credentials): AccessToken
    {
        $user = SanctumUser::query()->where('email', $credentials->email)->first();

        if (!$user || !$this->hasher->check($credentials->password, $user->password)) {
            throw new InvalidCredentials();
        }

        return new AccessToken($user->createToken('spa-token')->plainTextToken);
    }
}
