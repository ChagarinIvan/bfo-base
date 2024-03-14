<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Middleware;

use App\Application\Dto\Auth\UserId;
use Closure;
use Illuminate\Contracts\Auth\Factory as AuthService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class UserIdMiddleware
{
    public function __construct(
        private Container $app,
        private AuthService $auth,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $userId = $this->auth->guard()->id();
        if ($userId) {
            $this->app->instance(UserId::class, new UserId($userId));
        }

        return $next($request);
    }
}
