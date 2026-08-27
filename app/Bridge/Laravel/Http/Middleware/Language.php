<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class Language
{
    public function __construct(private readonly UserService $localeService, private readonly Application $application)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $locale = $this->localeService->getLocale();
        $this->application->setLocale($locale);
        return $next($request);
    }
}
