<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class Language
{
    public function __construct(private readonly Application $application)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $this->application->setLocale('by');
        return $next($request);
    }
}
