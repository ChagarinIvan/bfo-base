<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider;

use App\Application\Handler\Event\DisableCompetitionHandler;
use App\Application\Handler\Event\DisableEventHandler;
use App\Domain\Competition\Event\CompetitionDisabled;
use App\Domain\Event\Event\EventDisabled;
use Illuminate\Support\ServiceProvider;

final class EventHandlerServiceProvider extends ServiceProvider
{
    protected $listen = [
        CompetitionDisabled::class => [DisableCompetitionHandler::class,],
        EventDisabled::class => [DisableEventHandler::class,],
    ];
}
