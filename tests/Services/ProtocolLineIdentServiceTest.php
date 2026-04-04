<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\ProtocolLineIdentService;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProtocolLineIdentServiceTest extends TestCase
{
    public static function prepareLineDataProvider(): Iterator
    {
        yield ['алена', 'алена'];
        yield ['елена', 'елена'];
        yield ['лена', 'елена'];
        yield ['алёна', 'алена'];
    }

    #[DataProvider('prepareLineDataProvider')]
    #[Test]
    public function prepare_line(string $name, string $expectedName): void
    {
        $this->assertSame($expectedName, ProtocolLineIdentService::prepareLine($name));
    }
}
