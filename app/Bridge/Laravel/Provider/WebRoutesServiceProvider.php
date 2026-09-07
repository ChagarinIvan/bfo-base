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
use App\Bridge\Laravel\Http\Controllers\Error\Show404ErrorAction;
use App\Bridge\Laravel\Http\Controllers\Error\ShowUnexpectedErrorAction;
use App\Bridge\Laravel\Http\Controllers\Event\DeleteEventAction;
use App\Bridge\Laravel\Http\Controllers\Event\DownloadEventProtocolAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowCreateEventFormAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowEditEventFormAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowEventAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowEventDistanceAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowUnitEventsFormAction;
use App\Bridge\Laravel\Http\Controllers\Event\StoreEventAction;
use App\Bridge\Laravel\Http\Controllers\Event\UnitEventsAction;
use App\Bridge\Laravel\Http\Controllers\Event\UpdateEventAction;
use App\Bridge\Laravel\Http\Controllers\Login\MakeNewPasswordByTokenAction;
use App\Bridge\Laravel\Http\Controllers\Login\ShowLoginFormAction;
use App\Bridge\Laravel\Http\Controllers\Login\SignInAction;
use App\Bridge\Laravel\Http\Controllers\Login\SignOutAction;
use App\Bridge\Laravel\Http\Controllers\Person\DeletePersonAction;
use App\Bridge\Laravel\Http\Controllers\Person\ExtractPersonAction;
use App\Bridge\Laravel\Http\Controllers\Person\SetProtocolLinePersonAction;
use App\Bridge\Laravel\Http\Controllers\Person\ShowSetPersonToProtocolLineAction;
use App\Bridge\Laravel\Http\Controllers\Registration\SendRegistrationDataAction;
use App\Bridge\Laravel\Http\Controllers\Registration\ShowRegistrationFormAction;
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

                //event
                $this->routeRegistrar->prefix('events')->group(function (): void {
                    $this->route->get('{eventId}', ShowEventAction::class);
                    $this->route->get('d/{distance}', ShowEventDistanceAction::class);

                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('{competitionId}/create', ShowCreateEventFormAction::class);
                        $this->route->post('{competitionId}/store', StoreEventAction::class);
                        $this->route->get('{competition}/sum', ShowUnitEventsFormAction::class);
                        $this->route->post('{competition}/unit', UnitEventsAction::class);
                        $this->route->get('{event}/delete', DeleteEventAction::class);
                        $this->route->get('{event}/edit', ShowEditEventFormAction::class);
                        $this->route->get('{event}/download', DownloadEventProtocolAction::class);
                        $this->route->post('{eventId}/update', UpdateEventAction::class);
                    });
                });

                //persons
                $this->routeRegistrar->prefix('persons')->group(function (): void {
                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('/{person}/delete', DeletePersonAction::class);

                        $this->route->get('person/{protocol}/show', ShowSetPersonToProtocolLineAction::class);
                        $this->route->get('{person}/{protocol}/set', SetProtocolLinePersonAction::class);
                        $this->route->get('extract/{protocol}/', ExtractPersonAction::class);
                    });
                });

                //errors
                $this->route->get('/404', Show404ErrorAction::class);
                $this->route->get('/500', ShowUnexpectedErrorAction::class);

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

                //auth group
                $this->route->get('/login', ShowLoginFormAction::class);
                $this->route->get('/login/auth/{token}', MakeNewPasswordByTokenAction::class);
                $this->route->post('/sign-in', SignInAction::class);
                $this->route->get('/sign-out', SignOutAction::class);

                $this->routeRegistrar->middleware(['auth'])->prefix('registration')->group(function (): void {
                    $this->route->get('', ShowRegistrationFormAction::class);
                    $this->route->post('/data', SendRegistrationDataAction::class);
                });
            });
        });
    }
}
