<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     */
    protected function redirectTo($request): ?string
    {
        if (!$request->expectsJson()) {
            return '/';
        }
        return null;
    }
}
