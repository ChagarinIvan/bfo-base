<?php

namespace App\Providers;

use App\Http\Controllers\Api;
use App\Http\Controllers\FrontendApi;
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
        $rateLimiter = $this->app->make(RateLimiter::class);
        $rateLimiter->for('api', fn (Request $request) => Limit::perMinute(60));


        $this->routes(function () {
            $this->route->middleware('api')
                ->prefix('api')
                ->group(function () {
                    $this->route->get('competitions',                        [Api\CompetitionController::class, 'index']);
                    $this->route->get('competition/{competition_id}/events', [Api\EventsController::class, 'index']);
                    $this->route->get('event/{event_id}/results',            [Api\ResultsController::class, 'index']);
                    $this->route->get('clubs',                               [Api\ClubsController::class, 'index']);
                    $this->route->get('persons',                             [Api\PersonsController::class, 'index']);

                    //frontend-api
                    $this->route->prefix('frontend')->group(function () {
                        $this->route->resource('person', FrontendApi\PersonController::class);
                        $this->route->resource('club', FrontendApi\ClubController::class,)->only(['index']);
                    });
                });
        });
    }
}
