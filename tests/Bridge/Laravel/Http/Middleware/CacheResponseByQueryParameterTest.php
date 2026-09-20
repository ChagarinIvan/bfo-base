<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Middleware;

use App\Bridge\Laravel\Http\Middleware\CacheResponseByQueryParameter;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class CacheResponseByQueryParameterTest extends TestCase
{
    #[Test]
    public function it_caches_a_response_when_the_configured_query_parameter_is_present(): void
    {
        $response = new CacheResponseByQueryParameter()->handle(
            Request::create('/api/v1/cup-events?eventIds[]=1'),
            static fn (): Response => new Response('ok'),
            'eventIds',
            86400,
        );

        $this->assertSame('max-age=86400, private', $response->headers->get('Cache-Control'));
    }

    #[Test]
    public function it_leaves_unmatched_requests_uncached(): void
    {
        $response = new CacheResponseByQueryParameter()->handle(
            Request::create('/api/v1/cup-events?cupId=1'),
            static fn (): Response => new Response('ok'),
            'eventIds',
            86400,
        );

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
    }
}
