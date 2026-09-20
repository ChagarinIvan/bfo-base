<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider;

use App\Bridge\Laravel\Http\Controllers\Cup\ClearCacheAction;
use App\Bridge\Laravel\Http\Controllers\Cup\DeleteCupAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ExportCupGroupTableAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ExportCupTableAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupTableAction;
use App\Bridge\Laravel\Http\Controllers\CupEvents\DeleteCupEventAction;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\RouteRegistrar;

class WebRoutesServiceProvider extends ServiceProvider
{
    private Redirector $redirector;
    private Registrar $route;
    private RouteRegistrar $routeRegistrar;

    public function boot(): void
    {
        $this->redirector = $this->app->make(Redirector::class);
        $this->route = $this->app->make(Registrar::class);
        $this->routeRegistrar = $this->app->make(RouteRegistrar::class);

        $this->routes(function (): void {
            $this->routeRegistrar->middleware('web')->group(function (): void {
                $this->route->get('', fn () => $this->redirector->to('/app/competitions'));

                //cups
                $this->routeRegistrar->prefix('cups')->group(function (): void {
                    $this->route->get('{cupId}/cache', ClearCacheAction::class);
                    $this->route->get('{cup}/{group}/table', ShowCupTableAction::class);

                    //old auth
                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('{cup}/{group}/table-export', ExportCupGroupTableAction::class);
                        $this->route->get('{cup}/export', ExportCupTableAction::class);
                        $this->route->get('{cupId}/delete', DeleteCupAction::class);
                        $this->route->get('{cupId}/{event}/delete', DeleteCupEventAction::class);
                    });
                });
            });
        });
    }
}
