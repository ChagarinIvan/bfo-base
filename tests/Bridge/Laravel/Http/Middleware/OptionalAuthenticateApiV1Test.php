<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Middleware;

use App\Application\Dto\Auth\UserId;
use App\Bridge\Laravel\Http\Middleware\OptionalAuthenticateApiV1;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class OptionalAuthenticateApiV1Test extends TestCase
{
    #[Test]
    public function it_allows_anonymous_requests_without_registering_user_id(): void
    {
        $guard = $this->createMock(Guard::class);
        $guard->expects($this->once())->method('user')->willReturn(null);
        $auth = $this->authFactory($guard);
        $request = Request::create('/api/v1/competitions');

        $response = new OptionalAuthenticateApiV1($auth, app())->handle(
            $request,
            static fn (): Response => new Response('ok'),
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNull($request->user());
        $this->assertFalse(app()->bound(UserId::class));
    }

    #[Test]
    public function it_resolves_user_and_user_id_for_a_valid_bearer_token(): void
    {
        $user = new SanctumUser();
        $user->id = 42;
        $guard = $this->createMock(Guard::class);
        $guard->expects($this->once())->method('user')->willReturn($user);
        $guard->expects($this->once())->method('id')->willReturn(42);
        $auth = $this->authFactory($guard);
        $request = Request::create('/api/v1/competitions');

        $response = new OptionalAuthenticateApiV1($auth, app())->handle(
            $request,
            function (Request $nextRequest) use ($user): Response {
                $this->assertSame($user, $nextRequest->user());

                return new Response('ok');
            },
        );

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
