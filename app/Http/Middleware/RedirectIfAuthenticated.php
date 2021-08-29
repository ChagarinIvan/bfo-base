<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Login\ShowLoginFormAction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\Auth\Factory as AuthService;

class RedirectIfAuthenticated
{
    protected Redirector $redirector;
    private AuthService $authService;

    public function __construct(Redirector $redirector, AuthService $authService)
    {
        $this->redirector = $redirector;
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if ($this->authService->guard($guard)->check()) {
                return  $this->redirector->action(ShowLoginFormAction::class);
            }
        }

        return $next($request);
    }
}
