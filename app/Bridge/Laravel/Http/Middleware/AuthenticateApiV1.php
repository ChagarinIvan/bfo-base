<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Middleware;

use App\Application\Dto\Auth\UserId;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class AuthenticateApiV1
{
    public function __construct(
        private AuthFactory $auth,
        private ConfigRepository $config,
        private Container $container,
    )
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->container->forgetInstance(UserId::class);

        $user = $this->bearerUser($request);
        if (!$user instanceof Authenticatable) {
            return response()->json(['errors' => [[
                'code' => 'unauthenticated',
                'message' => 'Unauthenticated.',
            ]]], Response::HTTP_UNAUTHORIZED);
        }

        $request->setUserResolver(static fn (): Authenticatable => $user);
        $this->container->instance(UserId::class, new UserId((int) $user->getAuthIdentifier()));

        return $next($request);
    }

    private function bearerUser(Request $request): ?Authenticatable
    {
        if ($request->bearerToken() === null) {
            $user = $this->auth->guard('sanctum')->user();

            return $user instanceof Authenticatable ? $user : null;
        }

        $guards = $this->config->get('sanctum.guard');
        $this->config->set('sanctum.guard', []);

        try {
            $user = $this->auth->guard('sanctum')->user();

            return $user instanceof Authenticatable ? $user : null;
        } finally {
            $this->config->set('sanctum.guard', $guards);
        }
    }
}
