<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Middleware;

use App\Application\Dto\Auth\UserId;
use App\Bridge\Laravel\Http\Middleware\AuthenticateApiV1;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class AuthenticateApiV1Test extends TestCase
{
    #[Test]
    public function it_returns_api_401_when_user_is_not_authenticated(): void
    {
        $guard = $this->createMock(Guard::class);
        $guard->expects($this->once())->method('user')->willReturn(null);
        $auth = $this->authFactory($guard);
        $nextCalled = false;
        $next = static function () use (&$nextCalled): Response {
            $nextCalled = true;

            return new Response();
        };

        $response = (new AuthenticateApiV1($auth, app()))->handle(Request::create('/api/v1/users'), $next);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertSame([
            'errors' => [[
                'code' => 'unauthenticated',
                'message' => 'Unauthenticated.',
            ]],
        ], $response->getData(true));
        $this->assertFalse($nextCalled);
    }

    #[Test]
    public function it_sets_request_user_and_user_id_before_calling_next(): void
    {
        $user = new SanctumUser();
        $user->id = 42;
        $guard = $this->createMock(Guard::class);
        $guard->expects($this->once())->method('user')->willReturn($user);
        $guard->expects($this->once())->method('id')->willReturn(42);
        $auth = $this->authFactory($guard);
        $request = Request::create('/api/v1/users');
        $next = function (Request $nextRequest) use ($user): Response {
            $this->assertSame($user, $nextRequest->user());

            return new Response('ok');
        };

        $response = (new AuthenticateApiV1($auth, app()))->handle($request, $next);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(42, app()->make(UserId::class)->id);
    }

    /** @param Guard&MockObject $guard */
    private function authFactory(Guard $guard): AuthFactory&MockObject
    {
        $auth = $this->createMock(AuthFactory::class);
        $auth->expects($this->atLeastOnce())->method('guard')->with('sanctum')->willReturn($guard);

        return $auth;
    }
}
