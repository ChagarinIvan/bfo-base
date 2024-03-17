<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider;

use App\Application\Handler\Event\DisableCompetitionHandler;
use App\Application\Handler\Event\DisableEventHandler;
use App\Application\Handler\PersonPrompt\PersonCreatedHandler;
use App\Application\Handler\PersonPrompt\PersonInfoUpdatedHandler;
use App\Domain\Competition\Event\CompetitionDisabled;
use App\Domain\Event\Event\EventDisabled;
use App\Domain\Person\Event\PersonCreated;
use App\Domain\Person\Event\PersonInfoUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

final class EventHandlerServiceProvider extends EventServiceProvider
{
    protected $listen = [
        CompetitionDisabled::class => [DisableCompetitionHandler::class,],
        EventDisabled::class => [DisableEventHandler::class,],
        PersonCreated::class => [PersonCreatedHandler::class,],
        PersonInfoUpdated::class => [PersonInfoUpdatedHandler::class,],
    ];

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
