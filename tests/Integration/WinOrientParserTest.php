<?php

namespace Tests\Integration;

use App\Models\Parser\WinOrientHtmlParser;

class WinOrientParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return WinOrientHtmlParser::class;
    }

    protected function getFilePath(): string
    {
        return '2020/18072020.htm';
    }

    protected function getResults(): array
    {
        return [
            1 => [
                'Михалкин',
                'Игорь',
                'Эридан',
                2008,
                null,
                141,
                '00:27:39',
                1,
                null,
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 118;
    }
}
