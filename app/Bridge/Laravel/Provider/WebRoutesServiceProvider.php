<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider;

use App\Bridge\Laravel\Http\Controllers\Cup\ClearCacheAction;
use App\Bridge\Laravel\Http\Controllers\Cup\DeleteCupAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ExportCupGroupTableAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ExportCupTableAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ShowCreateCupFormAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupEventGroupAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupsListAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupTableAction;
use App\Bridge\Laravel\Http\Controllers\Cup\ShowEditCupFormAction;
use App\Bridge\Laravel\Http\Controllers\Cup\StoreCupAction;
use App\Bridge\Laravel\Http\Controllers\Cup\UpdateCupAction;
use App\Bridge\Laravel\Http\Controllers\CupEvents\DeleteCupEventAction;
use App\Bridge\Laravel\Http\Controllers\CupEvents\ShowCreateCupEventFormAction;
use App\Bridge\Laravel\Http\Controllers\CupEvents\ShowEditCupEventFormAction;
use App\Bridge\Laravel\Http\Controllers\CupEvents\StoreCupEventAction;
use App\Bridge\Laravel\Http\Controllers\CupEvents\UpdateCupEventAction;
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
                    $this->route->get('', ShowCupsListAction::class);
                    $this->route->get('{cupId}/show', ShowCupAction::class);
                    $this->route->get('{cupId}/cache', ClearCacheAction::class);
                    $this->route->get('{cup}/{group}/table', ShowCupTableAction::class);
                    $this->route->get('{cup}/{event}/{group}/show', ShowCupEventGroupAction::class);

                    //old auth
                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('{cup}/{group}/table-export', ExportCupGroupTableAction::class);
                        $this->route->get('{cup}/export', ExportCupTableAction::class);
                        $this->route->get('create', ShowCreateCupFormAction::class);
                        $this->route->post('store', StoreCupAction::class);
                        $this->route->get('{cupId}/edit', ShowEditCupFormAction::class);
                        $this->route->post('{cupId}/update', UpdateCupAction::class);
                        $this->route->get('{cupId}/delete', DeleteCupAction::class);
                        $this->route->get('{cupId}/event/create', ShowCreateCupEventFormAction::class);
                        $this->route->post('{cup}/event/store', StoreCupEventAction::class);
                        $this->route->get('{cupId}/{event}/delete', DeleteCupEventAction::class);
                        $this->route->get('{cupId}/{cupEventId}/edit', ShowEditCupEventFormAction::class);
                        $this->route->post('{cup}/{event}/update', UpdateCupEventAction::class);
                    });
                });
            });
        });
    }
}
