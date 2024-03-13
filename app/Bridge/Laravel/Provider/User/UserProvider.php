<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\User;

use App\Domain\User\UserRepository;
use App\Infrastracture\Laravel\Eloquent\User\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

final class UserProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
    }
}
