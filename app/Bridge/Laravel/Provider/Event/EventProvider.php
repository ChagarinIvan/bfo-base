<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Event;

use App\Domain\Event\EventRepository;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Factory\StandardEventFactory;
use App\Domain\Event\Factory\StoreProtocolEventFactory;
use App\Domain\Event\ProtocolPathResolver;
use App\Domain\Event\ProtocolStorage;
use App\Domain\Event\ProtocolUpdater;
use App\Domain\Event\StandardProtocolUpdater;
use App\Infrastracture\Laravel\Eloquent\Event\EloquentEventRepository;
use App\Infrastracture\Laravel\Storage\Event\FileProtocolStorage;
use Illuminate\Support\ServiceProvider;

final class EventProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(ProtocolUpdater::class, StandardProtocolUpdater::class);
        $this->app->bind(EventFactory::class, StandardEventFactory::class);
        $this->app->bind(ProtocolStorage::class, FileProtocolStorage::class);
        $this->app->bind(EventRepository::class, EloquentEventRepository::class);
        $this->app->bind(StandardEventFactory::class, StandardEventFactory::class);

        $this->app->bind(EventFactory::class, fn () => new StoreProtocolEventFactory(
            $this->app->get(StandardEventFactory::class),
            $this->app->get(ProtocolStorage::class),
            $this->app->get(ProtocolPathResolver::class),
        ));
    }
}
