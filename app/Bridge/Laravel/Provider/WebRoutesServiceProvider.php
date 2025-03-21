<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider;

use App\Bridge\Laravel\Http\Controllers\BackAction;
use App\Bridge\Laravel\Http\Controllers\Club;
use App\Bridge\Laravel\Http\Controllers\Competition;
use App\Bridge\Laravel\Http\Controllers\Cup;
use App\Bridge\Laravel\Http\Controllers\CupEvents;
use App\Bridge\Laravel\Http\Controllers\Error;
use App\Bridge\Laravel\Http\Controllers\Event;
use App\Bridge\Laravel\Http\Controllers\Faq;
use App\Bridge\Laravel\Http\Controllers\Flags;
use App\Bridge\Laravel\Http\Controllers\Groups;
use App\Bridge\Laravel\Http\Controllers\Login;
use App\Bridge\Laravel\Http\Controllers\Person;
use App\Bridge\Laravel\Http\Controllers\PersonPrompt;
use App\Bridge\Laravel\Http\Controllers\Rank;
use App\Bridge\Laravel\Http\Controllers\Registration;
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

    public function boot(): void
    {
        $this->redirector = $this->app->make(Redirector::class);
        $this->route = $this->app->make(Registrar::class);
        $this->routeRegistrar = $this->app->make(RouteRegistrar::class);

        $this->routes(function (): void {
            $this->routeRegistrar->middleware('web')->group(function (): void {
                $this->route->get('', fn () => $this->redirector->action(Competition\ShowCompetitionsListAction::class, ['year' => (string) Year::actualYear()->value]));
                $this->route->get('back', BackAction::class);

                //competitions
                $this->routeRegistrar->prefix('competitions')->group(function (): void {
                    $this->route->get('', Competition\ShowCompetitionsListAction::class);
                    $this->route->get('{competitionId}/show', Competition\ShowCompetitionAction::class);

                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('create', Competition\ShowCreateCompetitionFormAction::class);
                        $this->route->get('{competitionId}/edit', Competition\ShowEditCompetitionFormAction::class);
                        $this->route->get('{year}/{competitionId}/delete', Competition\DeleteCompetitionAction::class);
                        $this->route->post('store', Competition\StoreCompetitionAction::class);
                        $this->route->post('{competitionId}/update', Competition\UpdateCompetitionAction::class);
                    });
                });

                //event
                $this->routeRegistrar->prefix('events')->group(function (): void {
                    $this->route->get('{eventId}', Event\ShowEventAction::class);
                    $this->route->get('d/{distance}', Event\ShowEventDistanceAction::class);

                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('{competitionId}/create', Event\ShowCreateEventFormAction::class);
                        $this->route->post('{competitionId}/store', Event\StoreEventAction::class);
                        $this->route->get('{competition}/sum', Event\ShowUnitEventsFormAction::class);
                        $this->route->post('{competition}/unit', Event\UnitEventsAction::class);
                        $this->route->get('{event}/delete', Event\DeleteEventAction::class);
                        $this->route->get('{event}/edit', Event\ShowEditEventFormAction::class);
                        $this->route->get('{event}/download', Event\DownloadEventProtocolAction::class);
                        $this->route->post('{eventId}/update', Event\UpdateEventAction::class);
                        $this->route->get('{event}/add-flags', Event\ShowAddFlagToEventFormAction::class);
                        $this->route->get('{event}/{flag}/set', Event\AddFlagToEventAction::class);
                        $this->route->get('{event}/{flag}/delete', Event\DeleteEventFlagAction::class);
                    });
                });

                //persons
                $this->routeRegistrar->prefix('persons')->group(function (): void {
                    $this->route->get('', Person\ShowPersonsListAction::class);
                    $this->route->get('{person}/show', Person\ShowPersonAction::class);

                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('create', Person\ShowCreatePersonAction::class);
                        $this->route->post('store', Person\StorePersonAction::class);
                        $this->route->get('/{person}/edit', Person\ShowEditPersonAction::class);
                        $this->route->post('/{person}/update', Person\UpdatePersonAction::class);
                        $this->route->get('/{person}/delete', Person\DeletePersonAction::class);

                        $this->route->get('{personId}/prompts', PersonPrompt\ShowPersonPromptsListAction::class);
                        $this->route->get('{person}/payments', Person\ShowPersonPaymentsListAction::class);
                        $this->route->get('person/{protocol}/show', Person\ShowSetPersonToProtocolLineAction::class);
                        $this->route->get('{person}/{protocol}/set', Person\SetProtocolLinePersonAction::class);
                        $this->route->get('extract/{protocol}/', Person\ExtractPersonAction::class);

                        //person prompts
                        $this->routeRegistrar
                            ->prefix('prompt')
                            ->group(function (): void {
                                $this->route->get('{person}/create', PersonPrompt\ShowCreatePromptAction::class);
                                $this->route->post('{person}/store', PersonPrompt\StorePromptAction::class);
                                $this->route->get('{prompt}/edit', PersonPrompt\ShowEditPromptAction::class);
                                $this->route->post('{prompt}/update', PersonPrompt\UpdatePromptAction::class);
                                $this->route->get('{prompt}/delete', PersonPrompt\DeletePromptAction::class);
                            });
                    });
                });

                //ranks
                $this->routeRegistrar->prefix('ranks')->group(function (): void {
                    $this->route->get('list/{rank}', Rank\ShowRanksListAction::class);
                    $this->route->get('person/{personId}', Rank\ShowPersonRanksAction::class);
                    $this->route->get('check', Rank\ShowCheckPersonsRanksFormAction::class);
                    $this->route->post('check', Rank\CheckPersonsRanksAction::class);

                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('{rankId}/activate', Rank\ShowActivationFormAction::class);
                        $this->route->get('{rankId}/update-activation', Rank\ShowEditActivationDateFormAction::class);
                        $this->route->post('{rankId}/activate', Rank\ActivatePersonRankAction::class);
                        $this->route->post('{rankId}/update-activation', Rank\UpdateRankActivationDateAction::class);
                    });
                });

                //clubs
                $this->routeRegistrar->prefix('clubs')->group(function (): void {
                    $this->route->get('/', Club\ShowClubsListAction::class);
                    $this->route->get('{club}/show', Club\ShowClubAction::class);

                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('create', Club\ShowCreateClubFormAction::class);
                        $this->route->post('store', Club\StoreClubsAction::class);
                    });
                });

                //localization
                //only by locale
                // $this->route->get('/localization/{code}', Localization\ChangeLanguageAction::class);

                //flags
                $this->routeRegistrar->prefix('flags')->group(function (): void {
                    $this->route->get('{flag}/show', Flags\ShowFlagEventsAction::class);

                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('', Flags\ShowFlagsListAction::class);
                        $this->route->get('create', Flags\ShowCreateFlagFormAction::class);
                        $this->route->post('store', Flags\StoreFlagAction::class);
                        $this->route->get('{flag}/edit', Flags\ShowEditFlagFormAction::class);
                        $this->route->post('{flag}/update', Flags\UpdateFlagAction::class);
                        $this->route->get('{flag}/delete', Flags\DeleteFlagAction::class);
                    });
                });

                //faq
                $this->routeRegistrar->prefix('faq')->group(function (): void {
                    $this->route->get('', Faq\ShowFaqAction::class);
                    $this->route->get('api', Faq\ShowApiFaqAction::class);
                });

                //errors
                $this->route->get('/404', Error\Show404ErrorAction::class);
                $this->route->get('/500', Error\ShowUnexpectedErrorAction::class);

                //cups
                $this->routeRegistrar->prefix('cups')->group(function (): void {
                    $this->route->get('', Cup\ShowCupsListAction::class);
                    $this->route->get('{cupId}/show', Cup\ShowCupAction::class);
                    $this->route->get('{cupId}/cache', Cup\ClearCacheAction::class);
                    $this->route->get('{cup}/{group}/table', Cup\ShowCupTableAction::class);
                    $this->route->get('{cup}/{event}/{group}/show', Cup\ShowCupEventGroupAction::class);

                    //old auth
                    $this->middleware(['auth'])->group(function (): void {
                        $this->route->get('{cup}/{group}/table-export', Cup\ExportCupGroupTableAction::class);
                        $this->route->get('{cup}/export', Cup\ExportCupTableAction::class);
                        $this->route->get('create', Cup\ShowCreateCupFormAction::class);
                        $this->route->post('store', Cup\StoreCupAction::class);
                        $this->route->get('{cupId}/edit', Cup\ShowEditCupFormAction::class);
                        $this->route->post('{cupId}/update', Cup\UpdateCupAction::class);
                        $this->route->get('{cupId}/delete', Cup\DeleteCupAction::class);
                        $this->route->get('{cupId}/event/create', CupEvents\ShowCreateCupEventFormAction::class);
                        $this->route->post('{cup}/event/store', CupEvents\StoreCupEventAction::class);
                        $this->route->get('{cupId}/{event}/delete', CupEvents\DeleteCupEventAction::class);
                        $this->route->get('{cupId}/{cupEventId}/edit', CupEvents\ShowEditCupEventFormAction::class);
                        $this->route->post('{cup}/{event}/update', CupEvents\UpdateCupEventAction::class);
                    });
                });

                //groups
                $this->routeRegistrar->prefix('groups')->middleware(['auth'])->group(function (): void {
                    $this->route->get('', Groups\ShowGroupsListAction::class);
                    $this->route->get('{group}/delete', Groups\DeleteGroupAction::class);
                    $this->route->get('{group}', Groups\ShowGroupAction::class);
                    $this->route->get('{group}/unit', Groups\ShowUnitGroupsAction::class);
                    $this->route->post('{group}/unit', Groups\UnitGroupsAction::class);
                });

                //auth group
                $this->route->get('/login', Login\ShowLoginFormAction::class);
                $this->route->get('/login/auth/{token}', Login\MakeNewPasswordByTokenAction::class);
                $this->route->post('/sign-in', Login\SignInAction::class);
                $this->route->get('/sign-out', Login\SignOutAction::class);

                $this->routeRegistrar->middleware(['auth'])->prefix('registration')->group(function (): void {
                    $this->route->get('', Registration\ShowRegistrationFormAction::class);
                    $this->route->post('/data', Registration\SendRegistrationDataAction::class);
                });
            });
        });
    }
}
