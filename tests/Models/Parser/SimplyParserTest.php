<?php
declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\SimplyParser;

class SimplyParserTest extends AbstractParserTest
{
    public static function dataProvider(): array
    {
        return [
            [
                '2019/protocol_191026_otkrytyy-kubok-minskogo-rayona-po-sportivnomu-orientirovaniyu-belaya-rus-2019_72004.htm',
                319,
                [
                    0 => [
                        'Холод',
                        'Ирина',
                        'СКО «Орион»',
                        2009,
                        'Iю',
                        305,
                        '0:08:39',
                        1,
                        '-',
                        null,
                    ],
                    18 => [
                        'Ярошевич',
                        'Алена',
                        'КО «Случь»',
                        2011,
                        'б/р',
                        125,
                        null,
                        null,
                        '-',
                        null
                    ],
                ]
            ],
            [
                '2019/protocol_191027_otkrytyy-kubok-minskogo-rayona-po-sportivnomu-orientirovaniyu-belaya-rus-2019_72005.htm',
                329,
                [
                    0 => [
                        'Холод',
                        'Ирина',
                        'СКО «Орион»',
                        2009,
                        'Iю',
                        305,
                        '0:12:57',
                        1,
                        '-',
                        null,
                    ],
                    13 => [
                        'Малышко',
                        'Анна',
                        'КСО «Белая Русь»',
                        2013,
                        'б/р',
                        167,
                        null,
                        null,
                        '-',
                        null
                    ],
                ]
            ],
            [
                '2019/23032019.htm',
                296,
                [
                    0 => [
                        'Холод',
                        'Ирина',
                        'СКО «Орион»',
                        2009,
                        'Iю',
                        78,
                        '00:06:42',
                        1,
                        '-',
                        null,
                    ],
                    41 => [
                        'Дичковская',
                        'Алеся',
                        'КО «Случь»',
                        2003,
                        'I',
                        274,
                        null,
                        null,
                        '-',
                        null,
                    ],
                ]
            ],
            [
                '2019/protocol_190329_brestskiy-podsnezhnik-2019_37611.htm',
                521,
                [
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
                ]
            ]
        ];
    }
    protected function getParser(): string
    {
        return SimplyParser::class;
    }
}
