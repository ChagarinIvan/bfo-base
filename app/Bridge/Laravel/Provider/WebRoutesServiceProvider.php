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
use App\Bridge\Laravel\Http\Controllers\Event\AddFlagToEventAction;
use App\Bridge\Laravel\Http\Controllers\Event\DeleteEventAction;
use App\Bridge\Laravel\Http\Controllers\Event\DeleteEventFlagAction;
use App\Bridge\Laravel\Http\Controllers\Event\DownloadEventProtocolAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowAddFlagToEventFormAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowCreateEventFormAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowEditEventFormAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowEventAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowEventDistanceAction;
use App\Bridge\Laravel\Http\Controllers\Event\ShowUnitEventsFormAction;
use App\Bridge\Laravel\Http\Controllers\Event\StoreEventAction;
use App\Bridge\Laravel\Http\Controllers\Event\UnitEventsAction;
use App\Bridge\Laravel\Http\Controllers\Event\UpdateEventAction;
use App\Bridge\Laravel\Http\Controllers\Flags\DeleteFlagAction;
use App\Bridge\Laravel\Http\Controllers\Flags\ShowCreateFlagFormAction;
use App\Bridge\Laravel\Http\Controllers\Flags\ShowEditFlagFormAction;
use App\Bridge\Laravel\Http\Controllers\Flags\ShowFlagEventsAction;
use App\Bridge\Laravel\Http\Controllers\Flags\ShowFlagsListAction;
use App\Bridge\Laravel\Http\Controllers\Flags\StoreFlagAction;
use App\Bridge\Laravel\Http\Controllers\Flags\UpdateFlagAction;
use App\Bridge\Laravel\Http\Controllers\Groups\DeleteGroupAction;
use App\Bridge\Laravel\Http\Controllers\Groups\ShowGroupAction;
use App\Bridge\Laravel\Http\Controllers\Groups\ShowGroupsListAction;
use App\Bridge\Laravel\Http\Controllers\Groups\ShowUnitGroupsAction;
use App\Bridge\Laravel\Http\Controllers\Groups\UnitGroupsAction;
use App\Bridge\Laravel\Http\Controllers\Login\MakeNewPasswordByTokenAction;
use App\Bridge\Laravel\Http\Controllers\Login\ShowLoginFormAction;
use App\Bridge\Laravel\Http\Controllers\Login\SignInAction;
use App\Bridge\Laravel\Http\Controllers\Login\SignOutAction;
use App\Bridge\Laravel\Http\Controllers\Person\DeletePersonAction;
use App\Bridge\Laravel\Http\Controllers\Person\ExtractPersonAction;
use App\Bridge\Laravel\Http\Controllers\Person\SetProtocolLinePersonAction;
use App\Bridge\Laravel\Http\Controllers\Person\ShowCreatePersonAction;
use App\Bridge\Laravel\Http\Controllers\Person\ShowEditPersonAction;
use App\Bridge\Laravel\Http\Controllers\Person\ShowPersonAction;
use App\Bridge\Laravel\Http\Controllers\Person\ShowPersonsListAction;
use App\Bridge\Laravel\Http\Controllers\Person\ShowSetPersonToProtocolLineAction;
use App\Bridge\Laravel\Http\Controllers\Person\StorePersonAction;
use App\Bridge\Laravel\Http\Controllers\Person\UpdatePersonAction;
use App\Bridge\Laravel\Http\Controllers\PersonPayment\ShowCreatePersonPaymentAction;
use App\Bridge\Laravel\Http\Controllers\PersonPayment\ShowPersonPaymentsListAction;
use App\Bridge\Laravel\Http\Controllers\PersonPayment\StorePersonPaymentAction;
use App\Bridge\Laravel\Http\Controllers\PersonPrompt\DeletePromptAction;
use App\Bridge\Laravel\Http\Controllers\PersonPrompt\ShowCreatePromptAction;
use App\Bridge\Laravel\Http\Controllers\PersonPrompt\ShowEditPromptAction;
use App\Bridge\Laravel\Http\Controllers\PersonPrompt\ShowPersonPromptsListAction;
use App\Bridge\Laravel\Http\Controllers\PersonPrompt\StorePersonPromptAction;
use App\Bridge\Laravel\Http\Controllers\PersonPrompt\UpdatePersonPromptAction;
use App\Bridge\Laravel\Http\Controllers\Rank\ActivatePersonRankAction;
use App\Bridge\Laravel\Http\Controllers\Rank\ShowActivationFormAction;
use App\Bridge\Laravel\Http\Controllers\Rank\ShowEditActivationDateFormAction;
use App\Bridge\Laravel\Http\Controllers\Rank\ShowPersonRanksAction;
use App\Bridge\Laravel\Http\Controllers\Rank\UpdateRankActivationDateAction;
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
                        $this->route->get('{event}/add-flags', ShowAddFlagToEventFormAction::class);
                        $this->route->get('{event}/{flag}/set', AddFlagToEventAction::class);
                        $this->route->get('{event}/{flag}/delete', DeleteEventFlagAction::class);
                    });
                });

                //persons
                $this->routeRegistrar->prefix('persons')->group(function (): void {
                    $this->route->get('', ShowPersonsListAction::class);
                    $this->route->get('{person}/show', ShowPersonAction::class);

                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('create', ShowCreatePersonAction::class);
                        $this->route->post('store', StorePersonAction::class);
                        $this->route->get('/{person}/edit', ShowEditPersonAction::class);
                        $this->route->post('/{person}/update', UpdatePersonAction::class);
                        $this->route->get('/{person}/delete', DeletePersonAction::class);

                        $this->route->get('{personId}/prompts', ShowPersonPromptsListAction::class);
                        $this->route->get('{personId}/payments', ShowPersonPaymentsListAction::class);
                        $this->route->get('{personId}/payments/create', ShowCreatePersonPaymentAction::class);
                        $this->route->post('{personId}/payments/store', StorePersonPaymentAction::class);
                        $this->route->get('person/{protocol}/show', ShowSetPersonToProtocolLineAction::class);
                        $this->route->get('{person}/{protocol}/set', SetProtocolLinePersonAction::class);
                        $this->route->get('extract/{protocol}/', ExtractPersonAction::class);

                        //person prompts
                        $this->routeRegistrar
                            ->prefix('prompt')
                            ->group(function (): void {
                                $this->route->get('{personId}/create', ShowCreatePromptAction::class);
                                $this->route->post('{personId}/store', StorePersonPromptAction::class);
                                $this->route->get('{promptId}/edit', ShowEditPromptAction::class);
                                $this->route->post('{promptId}/update', UpdatePersonPromptAction::class);
                                $this->route->get('{promptId}/delete', DeletePromptAction::class);
                            });
                    });
                });

                //ranks
                $this->routeRegistrar->prefix('ranks')->group(function (): void {
                    $this->route->get('person/{personId}', ShowPersonRanksAction::class);

                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('{protocolLineId}/activate', ShowActivationFormAction::class);
                        $this->route->get('{protocolLineId}/update-activation', ShowEditActivationDateFormAction::class);
                        $this->route->post('{protocolLineId}/activate', ActivatePersonRankAction::class);
                        $this->route->post('{protocolLineId}/update-activation', UpdateRankActivationDateAction::class);
                    });
                });

                //clubs legacy bookmarks
                $this->route->get('clubs', fn () => $this->redirector->to('/app/clubs', 301));
                $this->route->get('clubs/create', fn () => $this->redirector->to('/app/clubs/create', 301));
                $this->route->get('clubs/{clubId}/show', fn (string $clubId) => $this->redirector->to("/app/clubs/{$clubId}", 301));

                //localization
                //only by locale
                // $this->route->get('/localization/{code}', Localization\ChangeLanguageAction::class);

                //flags
                $this->routeRegistrar->prefix('flags')->group(function (): void {
                    $this->route->get('{flag}/show', ShowFlagEventsAction::class);

                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('', ShowFlagsListAction::class);
                        $this->route->get('create', ShowCreateFlagFormAction::class);
                        $this->route->post('store', StoreFlagAction::class);
                        $this->route->get('{flag}/edit', ShowEditFlagFormAction::class);
                        $this->route->post('{flag}/update', UpdateFlagAction::class);
                        $this->route->get('{flag}/delete', DeleteFlagAction::class);
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

                //groups
                $this->routeRegistrar->prefix('groups')->middleware(['auth'])->group(function (): void {
                    $this->route->get('', ShowGroupsListAction::class);
                    $this->route->get('{group}/delete', DeleteGroupAction::class);
                    $this->route->get('{group}', ShowGroupAction::class);
                    $this->route->get('{group}/unit', ShowUnitGroupsAction::class);
                    $this->route->post('{group}/unit', UnitGroupsAction::class);
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
