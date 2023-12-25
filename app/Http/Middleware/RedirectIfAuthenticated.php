<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\Login\ShowLoginFormAction;
use Closure;
use Illuminate\Contracts\Auth\Factory as AuthService;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

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
     * @param Request $request
     * @param Closure $next
     * @param string|null ...$guards
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards): mixed
    {
        $otherGuards = empty($guards) ? [null] : $guards;

        foreach ($otherGuards as $guard) {
            if ($this->authService->guard($guard)->check()) {
                return  $this->redirector->action(ShowLoginFormAction::class);
            }
        }

        return $next($request);
    }
}
