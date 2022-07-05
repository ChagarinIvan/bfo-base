<?php

namespace Tests\Models\Parser;

use App\Models\Parser\OBelarusNetParser;

class OBelarusNetParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusNetParser::class;
    }

    public function dataProvider(): array
    {
        return [
            [
                '2021/protocol_210821_kubok-belarusi_84462.htm',
                127,
                [
                    0 => [
                        'Михалкин',
                        'Дмитрий',
                        'КСО «Эридан»',
                        1980,
                        'МС',
                        48,
                        '1:30:42',
                        1,
                        'МС',
                        100,
                    ],
                    1 => [
                        'Минаков',
                        'Александр',
                        'RUS КСО «Москомпас»',
                        1982,
                        'б/р',
                        156,
                        '1:33:53',
                        null,
                        '-',
                        null,
                        true
                    ],
                    45 => [
                        'Плеханенко',
                        'Виктор',
                        'Каденс',
                        1987,
                        'б/р',
                        148,
                        '1:51:08',
                        20,
                        'III',
                        null,
                    ],
                    64 => [
                        'Буковец',
                        'Артём',
                        'КСО «Три-О»',
                        null,
                        'б/р',
                        802,
                        '1:24:17',
                        11,
                        '-',
                        29,
                    ],
                    82 => [
                        'Буковец',
                        'Анатолий',
                        'КСО «Три-О»',
                        1950,
                        'I',
                        81,
                        null,
                        null,
                        '-',
                        null,
                    ],
                ]
            ],
            [
                '2020/20201202.html',
                36,
                [
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
                ]
            ],
            [
                '2020/202012166.html',
                43,
                [
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
                ]
            ]
        ];
    }

    protected function needConvert(): bool
    {
        return true;
    }
}
