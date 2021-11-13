<?php

namespace Tests\Integration;

use App\Services\ProtocolLineIdentService;
use Tests\TestCase;

class IdentServiceTest extends TestCase
{
    /**
     * @param string $name
     * @param string $expectedName
     * @dataProvider prepareLineDataProvider
     */
    public function testPrepareLine(string $name, string $expectedName): void
    {
        $this->assertEquals($expectedName, ProtocolLineIdentService::prepareLine($name));
    }

    public function prepareLineDataProvider(): array
    {
        return [
            ['алена', 'алена'],
            ['елена', 'елена'],
            ['лена', 'елена'],
            ['алёна', 'алена'],
        ];
    }
}
