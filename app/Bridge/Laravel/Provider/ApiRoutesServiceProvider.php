<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider;

use App\Bridge\Laravel\Http\Controllers\Api\ClubController;
use App\Bridge\Laravel\Http\Controllers\Api\CompetitionController;
use App\Bridge\Laravel\Http\Controllers\Api\EventsController;
use App\Bridge\Laravel\Http\Controllers\Api\ListPersonAction;
use App\Bridge\Laravel\Http\Controllers\Api\PersonController;
use App\Bridge\Laravel\Http\Controllers\Api\ResultsController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\RouteRegistrar;

class ApiRoutesServiceProvider extends ServiceProvider
{
    private RouteRegistrar $route;

    public function boot(): void
    {
        $this->route = $this->app->make(RouteRegistrar::class);

        $this->routes(function (): void {
            $this->route
                ->group(function (): void {
                    $this->route->get('/api/competitions', [CompetitionController::class, 'index']);
                    $this->route->get('/api/competition/{competition_id}/events', [EventsController::class, 'index']);
                    $this->route->get('/api/event/{event_id}/results', [ResultsController::class, 'index']);
                    $this->route->get('/api/person', [PersonController::class, 'index']);
                    $this->route->get('/api/persons', ListPersonAction::class);
                    $this->route->get('/api/club', [ClubController::class, 'index']);
                })
            ;
        });
    }
}
