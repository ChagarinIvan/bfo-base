<?php

declare(strict_types=1);

namespace App\Infrastructure\Integration\Obelarus;

use App\Domain\Event\Exception\InvalidProtocolContent;
use App\Domain\Event\Protocol\ProtocolUrlReader;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use RuntimeException;
use function in_array;
use function strlen;

final readonly class ObelarusProtocolUrlReader implements ProtocolUrlReader
{
    private const int MAX_BYTES = 10 * 1024 * 1024;

    public function __construct(private ClientInterface $client)
    {
    }

    public function read(string $url): string
    {
        try {
            $uri = new Uri($url);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidProtocolContent('invalid protocol URL', $exception->getCode(), previous: $exception);
        }

        if (
            !in_array($uri->getScheme(), ['http', 'https'], true)
            || $uri->getHost() !== 'obelarus.net'
            || $uri->getPort() !== null
            || $uri->getUserInfo() !== ''
        ) {
            throw new InvalidProtocolContent('unsupported protocol URL');
        }

        try {
            $response = $this->client->request('GET', $uri->withScheme('https')->withFragment(''), [
                'allow_redirects' => false,
                'connect_timeout' => 3.0,
                'timeout' => 5.0,
                'read_timeout' => 5.0,
                'proxy' => '',
                'stream' => true,
                'verify' => true,
            ]);
            $body = $response->getBody();
            try {
                if ($response->getStatusCode() !== 200) {
                    throw new InvalidProtocolContent('protocol download failed');
                }
                $content = Utils::copyToString($body, self::MAX_BYTES + 1);
                if (strlen($content) > self::MAX_BYTES) {
                    throw new InvalidProtocolContent('protocol download is too large');
                }
                if (!$body->eof()) {
                    throw new InvalidProtocolContent('incomplete protocol download');
                }
            } finally {
                $body->close();
            }
        } catch (GuzzleException|RuntimeException $exception) {
            throw new InvalidProtocolContent('protocol download failed', $exception->getCode(), previous: $exception);
        }

        return $content;
    }
}
