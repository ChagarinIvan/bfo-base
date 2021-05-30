<?php

namespace Tests\Integration\OParser;

use App\Models\Parser\OParser;
use Tests\Integration\AbstractParserTest;

class OParserTest1 extends AbstractParserTest
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
                false,
                'Ж12',
            ],
            11 => [
                'Мурашко',
                'Злата',
                'СКО Орион',
                2013,
                'б/р',
                700,
                '0:38:01',
                null,
                '-',
                null,
                true,
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
                false,
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
            51 => [
                'Кляусова',
                'Ксения',
                'Гомельская обл.',
                2007,
                'Iю',
                237,
                '0:26:38',
                1,
                'Iю',
                '1000',
                false,
                'Ж14',
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 520;
    }
}
