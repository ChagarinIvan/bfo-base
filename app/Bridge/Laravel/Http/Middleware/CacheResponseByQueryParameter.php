<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CacheResponseByQueryParameter
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next, string $parameter, int $maxAge): Response
    {
        $response = $next($request);

        if ($request->query->has($parameter)) {
            $response->setPublic();
            $response->setMaxAge($maxAge);
        }

        return $response;
    }
}
