<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\ListUsersAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\LoginAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\LogoutAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\CreateCompetitionAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\ListCompetitionsAction;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

final class ApiV1RoutesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $router = $this->app->make(Router::class);

        $this->routes(static function () use ($router): void {
            $router->prefix('api/v1')->post('auth/login', LoginAction::class);
            $router->prefix('api/v1')->get('competitions', ListCompetitionsAction::class);
            $router->prefix('api/v1')->middleware('auth:sanctum')->group(static function () use ($router): void {
                $router->delete('auth/logout', LogoutAction::class);
                $router->get('users', ListUsersAction::class);
                $router->post('competitions', CreateCompetitionAction::class);
            });
        });
    }
}
