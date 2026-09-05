<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\ListUsersAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\LoginAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\LogoutAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Club\CreateClubAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Club\ListAllClubAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Club\ListClubsAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Club\UpdateClubAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Club\ViewClubAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\CreateCompetitionAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\DeleteCompetitionAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\ListCompetitionsAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\UpdateCompetitionAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\ViewCompetitionAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Event\ListEventsAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Group\DeleteGroupAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Group\ListGroupsAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Group\MergeGroupsAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Group\UpdateGroupAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Group\ViewGroupAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ListPersonsAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ViewPersonAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPrompt\CreatePersonPromptAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPrompt\DeletePersonPromptAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPrompt\ListPersonPromptsAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPrompt\UpdatePersonPromptAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPrompt\ViewPersonPromptAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Rank\ListRanksAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Year\ListYearsAction;
use App\Bridge\Laravel\Http\Middleware\AuthenticateApiV1;
use App\Bridge\Laravel\Http\Middleware\OptionalAuthenticateApiV1;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

final class ApiV1RoutesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $router = $this->app->make(Router::class);

        $this->routes(static function () use ($router): void {
            $router->prefix('api/v1')->middleware('throttle:10,1')->post('auth/login', LoginAction::class);

            $router->prefix('api/v1')->middleware(OptionalAuthenticateApiV1::class)->group(static function () use ($router): void {
                $router->get('competitions', ListCompetitionsAction::class);
                $router->get('competitions/{competitionId}', ViewCompetitionAction::class);
                $router->get('clubs', ListClubsAction::class);
                $router->get('clubs/all', ListAllClubAction::class);
                $router->get('clubs/{clubId}', ViewClubAction::class);
                $router->get('groups', ListGroupsAction::class);
                $router->get('groups/{groupId}', ViewGroupAction::class);
                $router->get('events', ListEventsAction::class);
                $router->get('persons', ListPersonsAction::class);
                $router->get('persons/{personId}', ViewPersonAction::class);
            });

            $router->prefix('api/v1')->get('ranks', ListRanksAction::class);
            $router->prefix('api/v1')->get('years', ListYearsAction::class);

            $router->prefix('api/v1')->middleware(AuthenticateApiV1::class)->group(static function () use ($router): void {
                $router->delete('auth/logout', LogoutAction::class);
                $router->get('users', ListUsersAction::class);
                $router->post('clubs', CreateClubAction::class);
                $router->put('clubs/{clubId}', UpdateClubAction::class);
                $router->put('groups/{groupId}', UpdateGroupAction::class);
                $router->delete('groups/{groupId}', DeleteGroupAction::class);
                $router->get('person-prompts', ListPersonPromptsAction::class);
                $router->get('person-prompts/{promptId}', ViewPersonPromptAction::class);
                $router->post('persons/{personId}/prompts', CreatePersonPromptAction::class);
                $router->put('person-prompts/{promptId}', UpdatePersonPromptAction::class);
                $router->delete('person-prompts/{promptId}', DeletePersonPromptAction::class);
                $router->post('groups/{sourceGroupId}/merge', MergeGroupsAction::class);
                $router->post('competitions', CreateCompetitionAction::class);
                $router->put('competitions/{competitionId}', UpdateCompetitionAction::class);
                $router->delete('competitions/{competitionId}', DeleteCompetitionAction::class);
            });
        });
    }
}
