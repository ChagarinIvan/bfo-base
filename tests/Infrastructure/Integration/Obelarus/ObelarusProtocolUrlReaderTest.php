<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Integration\Obelarus;

use App\Domain\Event\Exception\InvalidProtocolContent;
use App\Infrastructure\Integration\Obelarus\ObelarusProtocolUrlReader;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use function str_repeat;

final class ObelarusProtocolUrlReaderTest extends TestCase
{
    #[Test]
    public function it_loads_the_approved_host_over_https_with_redirects_disabled(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('request')
            ->willReturnCallback(function (string $method, UriInterface $uri, array $options): Response {
                $this->assertSame('GET', $method);
                $this->assertSame('https://obelarus.net/results?event=1', (string) $uri);
                $this->assertFalse($options['allow_redirects']);
                $this->assertTrue($options['verify']);

                return new Response(200, [], 'results');
            })
        ;

        $content = new ObelarusProtocolUrlReader($client)->read('http://obelarus.net/results?event=1#page');

        $this->assertSame('results', $content);
    }

    #[Test]
    public function it_rejects_other_hosts_without_sending_a_request(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())->method('request');
        $this->expectException(InvalidProtocolContent::class);

        new ObelarusProtocolUrlReader($client)->read('https://example.test/results');
    }

    #[Test]
    public function it_rejects_redirect_responses(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $client->method('request')->willReturn(new Response(302, ['Location' => 'https://example.test']));
        $this->expectException(InvalidProtocolContent::class);

        new ObelarusProtocolUrlReader($client)->read('https://obelarus.net/results');
    }

    #[Test]
    public function it_rejects_oversized_protocols(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $client->method('request')->willReturn(new Response(200, [], str_repeat('x', 10 * 1024 * 1024 + 1)));
        $this->expectException(InvalidProtocolContent::class);

        new ObelarusProtocolUrlReader($client)->read('https://obelarus.net/results');
    }
}
