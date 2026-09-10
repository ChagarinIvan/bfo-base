<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\User;

use App\Domain\Auth\CurrentTokenRevoker;
use App\Domain\Auth\Factory\UserFactory;
use App\Domain\Auth\LoginAuthenticator;
use App\Domain\Auth\PasswordGenerator;
use App\Domain\Auth\PasswordHasher;
use App\Domain\Auth\UserRepository;
use App\Infrastructure\Laravel\Auth\LaravelPasswordGenerator;
use App\Infrastructure\Laravel\Auth\LaravelPasswordHasher;
use App\Infrastructure\Laravel\Eloquent\User\EloquentUserFactory;
use App\Infrastructure\Laravel\Eloquent\User\EloquentUserRepository;
use App\Infrastructure\Sanctum\SanctumCurrentTokenRevoker;
use App\Infrastructure\Sanctum\SanctumLoginAuthenticator;
use Illuminate\Support\ServiceProvider;

final class AuthProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(UserFactory::class, EloquentUserFactory::class);
        $this->app->bind(PasswordGenerator::class, LaravelPasswordGenerator::class);
        $this->app->bind(PasswordHasher::class, LaravelPasswordHasher::class);
        $this->app->bind(LoginAuthenticator::class, SanctumLoginAuthenticator::class);
        $this->app->bind(CurrentTokenRevoker::class, SanctumCurrentTokenRevoker::class);
    }
}
