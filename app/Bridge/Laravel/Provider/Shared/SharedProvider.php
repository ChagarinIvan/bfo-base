<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Shared;

use App\Domain\Shared\ActualClock;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;
use App\Infrastracture\Laravel\Eloquent\Shared\EloquentTransactionalManager;
use Illuminate\Support\ServiceProvider;

final class SharedProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(Clock::class, ActualClock::class);
        $this->app->bind(TransactionManager::class, EloquentTransactionalManager::class);
    }
}
