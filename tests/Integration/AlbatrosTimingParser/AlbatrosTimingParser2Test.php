<?php

namespace Tests\Integration\AlbatrosTimingParser;

use App\Models\Parser\AlbatrosTimingParser;
use Tests\Integration\AbstractParserTest;

class AlbatrosTimingParser2Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return AlbatrosTimingParser::class;
    }

    protected function getFilePath(): string
    {
        return '2020/20201024_r.htm';
    }

    protected function getResults(): array
    {
        return [
            2 => [
                'Катлерова',
                'Надежда',
                'КО «БГУ»',
                1981,
                'II',
                46,
                null,
                null,
                '-',
                null,
            ],
            3 => [
                'Сукневич',
                'София',
                'КСО «Немига-Норд»',
                2010,
                'Iю',
                146,
                '0:24:22',
                1,
                '-',
                null
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 232;
    }
}
