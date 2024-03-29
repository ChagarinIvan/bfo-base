<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\OParser;

final class OParserTest extends AbstractParser
{
    public static function dataProvider(): array
    {
        return [
            [
                '2021/20210402_mid_brest.html',
                520,
                [
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
                    164 => [
                        'Шпаковская',
                        'Карина',
                        'КО Случь',
                        2000,
                        'КМС',
                        584,
                        '0:59:15',
                        1,
                        'I',
                        '1000',
                        false,
                        'Ж21А',
                    ],
                ]
            ],
            [
                '2021/20210403_kl_brest.html',
                530,
                [
                    0 => [
                        'Холод',
                        'Ирина',
                        'СКО Орион',
                        2009,
                        'б/р',
                        505,
                        '0:20:23',
                        1,
                        'IIю',
                        1000,
                    ],
                    15 => [
                        'Мурашко',
                        'Злата',
                        'СКО Орион',
                        2013,
                        'б/р',
                        700,
                        '0:43:19',
                        null,
                        '-',
                        null,
                        true,
                    ],
                    42 => [
                        'Мурашко',
                        'Полина',
                        'СКО Орион',
                        2012,
                        'б/р',
                        701,
                        null,
                        null,
                        '-',
                        null,
                        true
                    ],
                ]
            ]
        ];
    }
    protected function getParser(): string
    {
        return OParser::class;
    }
}
