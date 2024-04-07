<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

final class EventHandlerServiceProvider extends EventServiceProvider
{
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
    protected function discoverEventsWithin(): array
    {
        return [
            $this->app->path('Application/Handler/Event'),
        ];
    }
}
