<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use function fclose;
use function fwrite;
use function str_contains;
use function stream_get_contents;
use function stream_set_timeout;
use function stream_socket_client;
use function stream_socket_get_name;
use function stream_socket_server;
use const PHP_BINARY;

final class ProductionPhpResponseTest extends TestCase
{
    #[Test]
    public function malformed_multipart_keeps_a_valid_response_and_logs_the_startup_warning(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $this->assertIsResource($socket);
        $address = stream_socket_get_name($socket, false);
        $this->assertIsString($address);
        fclose($socket);

        $root = __DIR__ . '/../../..';
        $server = new Process([
            PHP_BINARY,
            '-c', $root . '/enviroment/php/99-security.ini',
            '-d', 'output_buffering=0',
            '-d', 'error_log=/dev/stderr',
            '-S', $address,
            '-t', '.',
            '../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php',
        ], $root . '/public', [
            'APP_ENV' => 'testing',
            'APP_DEBUG' => 'false',
            'SESSION_DRIVER' => 'array',
            'CACHE_STORE' => 'array',
            'CACHE_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'LOG_CHANNEL' => 'stderr',
            'SENTRY_LARAVEL_DSN' => '',
            'SENTRY_DSN' => '',
        ]);
        $server->setTimeout(15);
        $server->start();

        try {
            $this->assertTrue($server->waitUntil(static fn (string $type, string $output): bool => str_contains($output, 'Development Server')));
            $client = stream_socket_client('tcp://' . $address, timeout: 5);
            $this->assertIsResource($client);
            stream_set_timeout($client, 10);
            fwrite($client, "POST /bitsight/internet_census/probe HTTP/1.1\r\nHost: localhost\r\nContent-Type: multipart/form-data\r\nContent-Length: 5\r\nConnection: close\r\n\r\nprobe");
            $response = stream_get_contents($client);
            fclose($client);

            $this->assertIsString($response);
            $this->assertStringStartsWith('HTTP/1.1 404', $response);
            $this->assertStringNotContainsString('PHP Request Startup', $response);
            $this->assertStringNotContainsString('Cannot modify header information', $response);
            $this->assertStringContainsString('Missing boundary in multipart/form-data', $server->getErrorOutput());
            $this->assertStringNotContainsString('Cannot modify header information', $server->getErrorOutput());
        } finally {
            $server->stop();
        }
    }
}
