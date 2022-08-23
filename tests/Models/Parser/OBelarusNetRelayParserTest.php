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
                ],
                true
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
                    //10  1209  Маслакова Наталья  КСО «Пеленг-Вымпел»  1954  01:35:12  01:35:12  10
                    222 => [
                        'Маслакова',
                        'Наталья',
                        'КСО «Пеленг-Вымпел»',
                        1954,
                        null,
                        1209,       //номер
                        '01:35:12',
                        10,         //место
                        null,
                        null,
                    ],
                    //11  1201  Ткачук Дарья  КО «Сильван люкс»  I  1981
                    223 => [
                        'Ткачук',
                        'Дарья',
                        'КО «Сильван люкс»',
                        1981,
                        'I',
                        1201, //номер
                        null,
                        null, //место
                        null,
                        null,
                    ],
                ],
                true
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
            ],
            [
                '2022/220821_res.htm',
                134,
                [
                    //1001  Жаховский Евгений  КСО «Эридан»  МС  00:41:43  00:41:43  1  МС  291
                    0 => [
                        'Жаховский',    //фамилия
                        'Евгений',      //имя
                        'КСО «Эридан»', //клуб
                        null,           //год
                        'МС',           //разряд
                        '1001',         //номер
                        '00:41:43',     //время
                        1,              //место
                        291,            //очки
                        'МС',           //выполненный разряд
                    ],
                    //3012  Кондратьев Артем  лично RUS  01:05:25  02:42:50 в/к  -  -
                    8 => [
                        'Кондратьев',   //фамилия
                        'Артем',        //имя
                        'лично RUS',    //клуб
                        null,           //год
                        null,           //разряд
                        '3012',         //номер
                        '01:05:25',     //время
                        null,              //место
                        null,            //очки
                        null,           //выполненный разряд
                        true            //в/к
                    ],
                    //1  Амеличкин Василий  КСО «Белая Русь»  I  00:46:45  1
                    103 => [
                        'Амеличкин',        //фамилия
                        'Василий',          //имя
                        'КСО «Белая Русь»', //клуб
                        null,               //год
                        'I',                //разряд
                        '1',                //номер
                        '00:46:45',         //время
                        1,                  //место
                        null,               //очки
                        null,               //выполненный разряд
                    ],
                ]
            ],
        ];
    }
}
