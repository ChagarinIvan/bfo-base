<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Event;

use App\Domain\Event\EventRepository;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\StandardEventFactory;
use App\Infrastracture\Laravel\Eloquent\Event\EloquentEventRepository;
use Illuminate\Support\ServiceProvider;

final class EventProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(EventFactory::class, StandardEventFactory::class);
        $this->app->bind(EventRepository::class, EloquentEventRepository::class);
    }
}
