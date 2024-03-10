<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\AlbatrosTimingParser;

final class AlbatrosTimingParserTest extends AbstractParser
{
    public static function dataProvider(): array
    {
        return [
            [
                '2020/200905_perv_00_.htm',
                118,
                [
                    7 => [
                        'Сукневич',
                        'Миша',
                        'КСО «Немига-Норд»',
                        2010,
                        'б/р',
                        151,
                        '0:35:43',
                        8,
                        '-',
                        null,
                    ],
                    20 => [
                        'Лабкович',
                        'Иван',
                        'Минская обл.',
                        2007,
                        'Iю',
                        444,
                        '0:31:24',
                        2,
                        'IIю',
                        100
                    ],
                ]
            ],
            [
                '2020/20201024_r.htm',
                232,
                [
                    2 => [
                        'Катлерова',
                        'Надежда',
                        'КО «БГУ»',
                        1981,
                        'II',
                        46,
                        null,
                        null,
                        '-',
                        null,
                    ],
                    3 => [
                        'Сукневич',
                        'София',
                        'КСО «Немига-Норд»',
                        2010,
                        'Iю',
                        146,
                        '0:24:22',
                        1,
                        '-',
                        null
                    ],
                    5 => [
                        'Саванович',
                        'Мария',
                        'КСО «Эридан»',
                        2011,
                        'б/р',
                        183,
                        '0:41:54',
                        null,
                        '-',
                        null,
                        true,
                    ],
                ]
            ],
            [
                '2021/210306_res.htm',
                52,
                [
                    0 => [
                        'Буякова',
                        'Оксана',
                        'КО «Сильван люкс»',
                        1990,
                        'МС',
                        9,
                        '1:28:04',
                        1,
                        'МС',
                        100,
                    ],
                    7 => [
                        'Языкова',
                        'Дарья',
                        'КСО «Немига-Норд»',
                        1993,
                        'МС',
                        2,
                        null,
                        null,
                        '-',
                        null
                    ],
                    13 => [
                        'Лисаченко',
                        'Ксения',
                        'Эврика',
                        2008,
                        'Iю',
                        19,
                        '1:06:57',
                        1,
                        '-',
                        null
                    ],
                    40 => [
                        'Лисаченко',
                        'Иван',
                        'МГТЭЦДиМ',
                        2005,
                        'Iю',
                        57,
                        '0:44:45',
                        1,
                        null,
                        null
                    ],
                ]
            ],
            [
                '2021/protocol_210522_komandnyy-chempionat_80865.htm',
                349,
                [
                    232 => [
                        'Алексеенок',
                        'Алексей',
                        'КСО «Эридан»',
                        1988,
                        'МС',
                        383,
                        '0:42:34',
                        null,
                        '-',
                        null,
                        true,
                    ],
                ]
            ],
            [
                '2022/221211_res.htm',
                88,
                [
                    // 1 Савченко Яна   OC Silwan Liuks  2004 I  5125  0:58:02  1  -  119
                    0 => [
                        'Савченко',        //lastname
                        'Яна',             //firstname
                        'OC Silwan Liuks', //club
                        2004,              //year
                        'I',               //rank
                        5125,              //runner_number
                        '0:58:02',         //time
                        1,                 //place
                        '-',               //complete_rank
                        119,               //points
                    ],
                    // 1 Лычков Игорь  СКО "Немига-Норд"  1983 МС  13  0:42:42  1  I  120
                    34 => [
                        'Лычков',            //lastname
                        'Игорь',             //firstname
                        'СКО "Немига-Норд"', //club
                        1983,                //year
                        'МС',                //rank
                        13,                  //runner_number
                        '0:42:42',           //time
                        1,                   //place
                        'I',                 //complete_rank
                        120,                 //points
                    ],
                ]
            ]
        ];
    }
    protected function getParser(): string
    {
        return AlbatrosTimingParser::class;
    }
}
