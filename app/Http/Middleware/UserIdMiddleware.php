<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class UserIdMiddleware
{
    public function __construct(private SessionGuard $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $userId = $this->auth->id();
        $request->merge(['userId' => $userId]);

        return $next($request);
    }
}
