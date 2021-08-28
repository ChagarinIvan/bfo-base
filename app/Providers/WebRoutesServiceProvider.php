<?php

namespace App\Providers;

use App\Http\Controllers\Competition;
use App\Http\Controllers\Event;
use App\Http\Controllers\Person;
use App\Http\Controllers\Club;
use App\Http\Controllers\Localization;
use App\Http\Controllers\Flags;
use App\Http\Controllers\Faq;
use App\Http\Controllers\Error;
use App\Http\Controllers\Cups;
use App\Http\Controllers\CupEvents;
use App\Http\Controllers\Login;
use App\Http\Controllers\Registration;
use App\Models\Year;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\RouteRegistrar;

class WebRoutesServiceProvider extends ServiceProvider
{
    private Redirector $redirector;
    private Registrar $route;
    private RouteRegistrar $routeRegistrar;

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->redirector = $this->app->make(Redirector::class);
        $this->route = $this->app->make(Registrar::class);
        $this->routeRegistrar = $this->app->make(RouteRegistrar::class);

        $this->routes(function () {
            $this->routeRegistrar->middleware('web')->group(function () {
                $this->route->get('', fn() => $this->redirector->action(Competition\ShowCompetitionsListAction::class, [Year::actualYear()]));

                //competitions
                $this->routeRegistrar->prefix('competitions')->group(function () {
                    $this->route->get('{year}',             Competition\ShowCompetitionsListAction::class);
                    $this->route->get('{competition}/show', Competition\ShowCompetitionAction::class);

                    $this->middleware(['auth'])->group(function () {
                        $this->route->get( '{year}/create',               Competition\ShowCreateCompetitionFormAction::class);
                        $this->route->get( '{year}/{competition}/delete', Competition\DeleteCompetitionAction::class);
                        $this->route->post('store',                       Competition\StoreCompetitionAction::class);
                    });
                });

                //event
                $this->routeRegistrar->prefix('events')->group(function () {
                    $this->route->get('{event}', Event\ShowEventAction::class);

                    $this->middleware(['auth'])->group(function () {
                        $this->route->get( '{competition}/create',  Event\ShowCreateEventFormAction::class);
                        $this->route->post('{competition}/store',   Event\StoreEventAction::class);
                        $this->route->get( '{competition}/sum',     Event\ShowUnitEventsFormAction::class);
                        $this->route->post('{competition}/unit',    Event\UnitEventsAction::class);
                        $this->route->get( '{event}/delete',        Event\DeleteEventAction::class);
                        $this->route->get( '{event}/edit',          Event\ShowEditEventFormAction::class);
                        $this->route->post('{event}/update',        Event\UpdateEventAction::class);
                        $this->route->get( '{event}/add-flags',     Event\ShowAddFlagToEventFormAction::class);
                        $this->route->get( '{event}/{flag}/set',    Event\AddFlagToEventAction::class);
                        $this->route->get( '{event}/{flag}/delete', Event\DeleteEventFlagAction::class);

                    });
                });

                //persons
                $this->routeRegistrar->prefix('persons')->group(function () {
                    $this->route->get('', Person\ShowPersonsListAction::class);
                    $this->route->get('{person}/show', Person\ShowPersonAction::class);
                    $this->route->get('{person}/rank', Person\ShowPersonRanksAction::class);

                    $this->middleware(['auth'])->group(function () {
                        $this->route->get( 'create',                  Person\ShowCreatePersonFormAction::class);
                        $this->route->post('store',                   Person\StorePersonAction::class);
                        $this->route->get( '{person}/edit',           Person\ShowEditPersonFormAction::class);
                        $this->route->post('{person}/update',         Person\UpdatePersonAction::class);
                        $this->route->get( '{person}/delete',         Person\DeletePersonAction::class);
                        $this->route->get( '{person}/protocol/show',  Person\ShowSetPersonToProtocolLineAction::class);
                        $this->route->get( '{person}/{protocol}/set', Person\SetProtocolLinePersonAction::class);
                    });
                });

                //clubs
                $this->routeRegistrar->prefix('club')->group(function () {
                    $this->route->get('',            Club\ShowClubsListAction::class);
                    $this->route->get('{club}/show', Club\ShowClubAction::class);
                });

                //localization
                $this->route->get('/localization/{code}', Localization\ChangeLanguageAction::class);

                //flags
                $this->routeRegistrar->prefix('flags')->group(function () {
                    $this->route->get('{flag}/show', Flags\ShowFlagEventsAction::class);

                    $this->middleware(['auth'])->group(function () {
                        $this->route->get( '',              Flags\ShowFlagsListAction::class);
                        $this->route->get( 'create',        Flags\ShowCreateFlagFormAction::class);
                        $this->route->post('store',         Flags\StoreFlagAction::class);
                        $this->route->get( '{flag}/edit',   Flags\ShowEditFlagFormAction::class);
                        $this->route->post('{flag}/update', Flags\UpdateFlagAction::class);
                        $this->route->get( '{flag}/delete', Flags\DeleteFlagAction::class);

                    });
                });

                //faq
                $this->routeRegistrar->prefix('faq')->group(function () {
                    $this->route->get('',    Faq\ShowFaqAction::class);
                    $this->route->get('api', Faq\ShowApiFaqAction::class);
                });

                //errors
                $this->route->get('/404', Error\Show404ErrorAction::class);

                //cups
                $this->routeRegistrar->prefix('cups')->group(function () {
                    //default route
                    $this->route->get('{year}',                     Cups\ShowCupsListAction::class);
                    $this->route->get('{cup}/show',                 Cups\ShowCupAction::class);
                    $this->route->get('{cup}/{group}/table',        Cups\ShowCupTableAction::class);
                    $this->route->get('{cup}/{event}/{group}/show', CupEvents\ShowCupEventGroupAction::class);

                    $this->middleware(['auth'])->group(function () {
                        $this->route->get( '{year}/create',        Cups\ShowCreateCupFormAction::class);
                        $this->route->post('store',                Cups\StoreCupAction::class);
                        $this->route->get( '{cup}/edit',           Cups\ShowEditCupFormAction::class);
                        $this->route->post('{cup}/update',         Cups\UpdateCupAction::class);
                        $this->route->get( '{cup}/delete',         Cups\DeleteCupAction::class);
                        $this->route->get( '{cup}/event/create',   CupEvents\ShowCreateCupEventFormAction::class);
                        $this->route->post('{cup}/event/store',    CupEvents\StoreCupEventAction::class);
                        $this->route->get( '{cup}/{event}/delete', CupEvents\DeleteCupEventAction::class);
                        $this->route->get( '{cup}/{event}/edit',   CupEvents\ShowEditCupEventFormAction::class);
                        $this->route->post('{cup}/{event}/update', CupEvents\UpdateCupEventAction::class);


                    });
                });

                //auth group
                $this->route->get( '/login',              Login\ShowLoginFormAction::class);
                $this->route->get( '/login/auth/{token}', Login\MakeNewPasswordByTokenAction::class);
                $this->route->post('/sign-in',            Login\AuthValidationAction::class);

                $this->routeRegistrar->middleware(['auth'])->group(function () {
                    $this->route->get( '/registration',      Registration\ShowRegistrationFormAction::class);
                    $this->route->post('/registration/data', Registration\SendRegistrationDataAction::class);
                    $this->route->post('/registration/data', Registration\SendRegistrationDataAction::class);
                });
            });
        });
    }
}
