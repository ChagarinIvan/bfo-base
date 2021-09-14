<?php

namespace Tests\Integration\OBelarusNetParser;

use App\Models\Parser\OBelarusNetParser;
use Tests\Integration\AbstractParserTest;

class OBelarusNetParser2Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusNetParser::class;
    }

    protected function getFilePath(): string
    {
        return '2020/20201202.html';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Куцун',
                'Надежда',
                'Минск',
                1982,
                'б/р',
                2162,
                '2:11:15',
                1,
                null,
                100,
            ],
            3 => [
                'Лебедева',
                'Елена',
                'КО "БГУ"',
                1960,
                'I',
                2120,
                '1:58:44',
                3,
                null,
                26,
            ],
            17 => [
                'Некипелов',
                'Алексей',
                'КСО "БНТУ"',
                1991,
                'б/р',
                10,
                null,
                null,
                null,
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 36;
    }
}
