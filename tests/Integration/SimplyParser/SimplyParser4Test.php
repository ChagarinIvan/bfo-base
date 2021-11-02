<?php

namespace Tests\Integration\SimplyParser;

use App\Models\Parser\SimplyParser;
use Tests\Integration\AbstractParserTest;

class SimplyParser4Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return SimplyParser::class;
    }

    protected function getFilePath(): string
    {
        return '2019/protocol_190329_brestskiy-podsnezhnik-2019_37611.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Томанова',
                'Валерия',
                'КО Случь',
                2007,
                'Iю',
                533,
                '0:10:40',
                1,
                'Iю',
                1000,
            ],
            35 => [
                'Бойко',
                'Елизавета',
                'Червенский р-н',
                2007,
                'б/р',
                680,
                null,
                null,
                '-',
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 521;
    }
}
