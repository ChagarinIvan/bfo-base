<?php

namespace Tests\Integration;

use App\Models\Parser\OParser;

class OParserTest extends AbstractParserTest
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
        return [
            0 => [
                'Волосевич',
                'Александра',
                'КО Случь',
                2010,
                'IIю',
                553,
                '0:22:21',
                1,
                'IIю',
                1000,
            ],
            47 => [
                'Иванькова',
                'анастасия',
                'Alias СШ№39 Гомель',
                2009,
                'б/р',
                7,
                null,
                null,
                '-',
                null,
            ],
            50 => [
                'Волкова',
                'Анастасия',
                'Могилев ОЦТ',
                2009,
                'б/р',
                458,
                null,
                null,
                '-',
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 520;
    }
}
