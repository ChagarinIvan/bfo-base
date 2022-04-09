<?php

namespace App\Providers;

use App\Http\Controllers\Api;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteRegistrar;

class ApiRoutesServiceProvider extends ServiceProvider
{
    private RouteRegistrar $route;

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->route = $this->app->make(RouteRegistrar::class);

        $this->routes(function () {
            $this->route
                ->group(function () {
                    $this->route->get('/api/competitions',                        [Api\CompetitionController::class, 'index']);
                    $this->route->get('/api/competition/{competition_id}/events', [Api\EventsController::class, 'index']);
                    $this->route->get('/api/event/{event_id}/results',            [Api\ResultsController::class, 'index']);
                    $this->route->get('/api/person', [Api\PersonController::class, 'index']);
                    $this->route->get('/api/club',   [Api\ClubController::class, 'index']);
                })
            ;
        });
    }
}
