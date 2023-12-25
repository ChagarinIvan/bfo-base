<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class Language
{
    private UserService $localeService;
    private Application $application;

    public function __construct(UserService $localeService, Application $application)
    {
        $this->localeService = $localeService;
        $this->application = $application;
    }

    public function handle(Request $request, Closure $next)
    {
        $locale = $this->localeService->getLocale();
        $this->application->setLocale($locale);
        return $next($request);
    }
}
