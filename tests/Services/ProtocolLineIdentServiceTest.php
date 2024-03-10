<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\ProtocolLineIdentService;
use PHPUnit\Framework\TestCase;

class ProtocolLineIdentServiceTest extends TestCase
{
    public static function prepareLineDataProvider(): array
    {
        return [
            ['алена', 'алена'],
            ['елена', 'елена'],
            ['лена', 'елена'],
            ['алёна', 'алена'],
        ];
    }
    /**
     * @param string $name
     * @param string $expectedName
     *
     * @dataProvider prepareLineDataProvider
     *
     * @test
     */
    public function prepare_line(string $name, string $expectedName): void
    {
        $this->assertEquals($expectedName, ProtocolLineIdentService::prepareLine($name));
    }
}
