<?php

namespace Tests\Integration\OBelarusNetParser;

use App\Models\Parser\OBelarusNetParser;
use Tests\Integration\AbstractParserTest;

class OBelarusNetParser3Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusNetParser::class;
    }

    protected function getFilePath(): string
    {
        return '2020/202012166.html';
    }

    protected function getResults(): array
    {
        return [
            3 => [
                'Коротких',
                'Елена',
                'Аматар',
                1984,
                'б/р',
                2163,
                '5:04:10',
                4,
                null,
                26,
            ],
            18 => [
                'Кухто',
                'Петр',
                'Минск, Баклан',
                1981,
                'КМС',
                201,
                '1:14:09',
                6,
                null,
                83,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 43;
    }
}
