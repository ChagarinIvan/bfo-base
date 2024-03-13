<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class UserIdMiddleware
{
    public function __construct(private AuthService $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $userId = $this->auth->guard()->id();
        dd($userId);
        $request->merge(['userId' => $userId]);

        return $next($request);
    }
}
