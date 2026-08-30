<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Middleware;

use App\Application\Dto\Auth\UserId;
use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class OptionalAuthenticateApiV1
{
    public function __construct(
        private AuthFactory $auth,
        private Container $container,
    )
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->auth->guard('sanctum')->user();
        if ($user) {
            $request->setUserResolver(static fn () => $user);
            $this->container->instance(UserId::class, new UserId((int) $this->auth->guard('sanctum')->id()));
        }

        return $next($request);
    }
}
