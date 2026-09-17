<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Event;

use App\Domain\Event\EventRepository;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Factory\StandardEventFactory;
use App\Domain\Event\Factory\StoreProtocolEventFactory;
use App\Domain\Event\ProtocolPathResolver;
use App\Domain\Event\ProtocolUpdater;
use App\Domain\Event\StandardProtocolUpdater;
use App\Domain\Event\UniteEventDataService;
use App\Domain\Shared\Storage;
use App\Infrastructure\Laravel\Eloquent\Event\EloquentEventRepository;
use App\Infrastructure\Laravel\Eloquent\Event\EloquentUniteEventDataService;
use Illuminate\Support\ServiceProvider;

final class EventProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(ProtocolUpdater::class, StandardProtocolUpdater::class);
        $this->app->bind(EventFactory::class, StandardEventFactory::class);
        $this->app->bind(EventRepository::class, EloquentEventRepository::class);
        $this->app->bind(UniteEventDataService::class, EloquentUniteEventDataService::class);
        $this->app->bind(StandardEventFactory::class, StandardEventFactory::class);

        $this->app->bind(EventFactory::class, fn (): StoreProtocolEventFactory => new StoreProtocolEventFactory(
            $this->app->get(StandardEventFactory::class),
            $this->app->get(Storage::class),
            $this->app->get(ProtocolPathResolver::class),
        ));
    }
}
