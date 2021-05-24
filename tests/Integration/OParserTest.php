<?php

namespace Tests\Integration;

use App\Models\Parser\OParser;

class OParserTest
{
    protected function getParser(): string
    {
        return OParser::class;
    }

    protected function getFilePath(): string
    {
        return '2021/20210402_mid_brest.html';
    }

    protected function getResults(): array
    {
        return [];
    }

    protected function geLinesCount(): int
    {
        return 207;
    }
}
