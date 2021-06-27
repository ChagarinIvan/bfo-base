<?php

namespace Tests\Unit;

use App\Services\IdentService;
use PHPUnit\Framework\TestCase;

class PreparedLineTest extends TestCase
{
    /**
     * @param string $name
     * @param string $expectedName
     * @dataProvider prepareLineDataProvider
     */
    public function testPrepareLine(string $name, string $expectedName): void
    {
        $this->assertEquals($expectedName, IdentService::prepareLine($name));
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
