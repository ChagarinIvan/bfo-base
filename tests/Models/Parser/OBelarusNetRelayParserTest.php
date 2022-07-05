<?php

namespace Tests\Models\Parser;

use App\Models\Parser\OBelarusNetRelayParser;

class OBelarusNetRelayParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusNetRelayParser::class;
    }

    public function dataProvider(): array
    {
        return [
            [
                '2020/200816_res.htm',
                207,
                [
                    0 => [
                        'Новиченко',
                        'Антон',
                        'КСО «Эридан»',
                        1997,
                        'МС',
                        1001,
                        '00:41:54',
                        1,
                        'МС',
                        291,
                    ],
                    47 => [
                        'Шванц',
                        'Алексей',
                        'КСО «Верас»',
                        1991,
                        'КМС',
                        3015,
                        null,
                        null,
                        '-',
                        null,
                    ],
                ]
            ],
            [
                '2021/210905_res.htm',
                225,
                [
                    18 => [
                        'Солодчук',
                        'Дарья',
                        'Гомельская обл.-2',
                        2009,
                        'б/р',
                        1117,
                        null,
                        null,
                        '-',
                        null,
                    ],
                    27 => [
                        'Ермолович',
                        'Полина',
                        'Минская область',
                        2005,
                        'б/р',
                        1152,
                        '01:01:53',
                        1,
                        null,
                        291,
                    ],
                    212 => [
                        'Белоусов',
                        'Александр',
                        'КСО «Эридан»',
                        1981,
                        'б/р',
                        3012,
                        '01:35:21',
                        null,
                        '-',
                        null,
                    ],
                    222 => [
                        'Маслакова',
                        'Наталья',
                        'КСО «Пеленг-Вымпел»',
                        1954,
                        null,
                        1209,
                        '01:35:12',
                        10,
                        null,
                        null,
                    ],
                    223 => [
                        'Ткачук',
                        'Дарья',
                        'КО «Сильван люкс»',
                        1981,
                        'I',
                        1201,
                        null,
                        null,
                        null,
                        null,
                    ],
                ]
            ],
            [
                '2021/20210717.html',
                38,
                [
                    0 => [
                        'Полина',
                        'Котова',
                        'КСА Каданьётта',
                        null,
                        null,
                        1004,
                        '01:27:26',
                        1,
                        null,
                        null,
                    ],
                    12 => [
                        'Владимир',
                        'Зеленин',
                        'КСА Каданьётта',
                        null,
                        null,
                        1002,
                        null,
                        null,
                        null,
                        null,
                    ],
                ]
            ]
        ];
    }
}
