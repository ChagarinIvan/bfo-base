<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\User;

use App\Domain\Auth\CurrentTokenRevoker;
use App\Domain\Auth\LoginAuthenticator;
use App\Domain\Auth\UserRepository;
use App\Infrastructure\Laravel\Eloquent\User\EloquentUserRepository;
use App\Infrastructure\Sanctum\SanctumCurrentTokenRevoker;
use App\Infrastructure\Sanctum\SanctumLoginAuthenticator;
use Illuminate\Support\ServiceProvider;

final class AuthProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(LoginAuthenticator::class, SanctumLoginAuthenticator::class);
        $this->app->bind(CurrentTokenRevoker::class, SanctumCurrentTokenRevoker::class);
    }
}
