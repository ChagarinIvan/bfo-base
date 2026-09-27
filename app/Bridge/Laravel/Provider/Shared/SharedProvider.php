<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Shared;

use App\Domain\Shared\ActualClock;
use App\Domain\Shared\Clock;
use App\Domain\Shared\IdentLineGenerator;
use App\Domain\Shared\StandardIdentLineGenerator;
use App\Domain\Shared\Storage;
use App\Domain\Shared\TransactionManager;
use App\Domain\Shared\UuidGenerator;
use App\Infrastructure\Laravel\Eloquent\Shared\EloquentTransactionalManager;
use App\Infrastructure\Laravel\Storage\FileStorage;
use App\Infrastructure\Laravel\StrUuidGenerator;
use Illuminate\Support\ServiceProvider;

final class SharedProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(Clock::class, ActualClock::class);
        $this->app->bind(TransactionManager::class, EloquentTransactionalManager::class);
        $this->app->bind(UuidGenerator::class, StrUuidGenerator::class);
        $this->app->bind(IdentLineGenerator::class, StandardIdentLineGenerator::class);
        $this->app->bind(Storage::class, FileStorage::class);
    }
}
