<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\OBelarusSpanParser;

class OBelarusSpanParserTest extends AbstractParserTest
{
    public static function dataProvider(): array
    {
        return [
            [
                '2022/20220203.html',
                171,
                [
                    0 => [
                        'Волосевич',
                        'Александра',
                        'Слуцкий р-н',
                        2010,
                        'Iю',
                        119,
                        '00:22:22',
                        1,
                    ],
                    17 => [
                        'Бурма',
                        'Елизавета',
                        'Логойский р-н',
                        2010,
                        'б/р',
                        98,
                        '01:19:42',
                        null,
                        null,
                        null,
                        true
                    ],
                    18 => [
                        'Плис',
                        'Евгения',
                        'Березинский р-н',
                        null,
                        'б/р',
                        200,
                        null,
                        null,
                        null,
                        null,
                    ],
                    19 => [
                        'Примаченок',
                        'Дарья',
                        'Березинский р-н',
                        2009,
                        'Iю',
                        39,
                        null,
                        null,
                        null,
                        null,
                    ],
                ]
            ],
            [
                '2022/2022-07-09.htm',
                76,
                [
                    0 => [
                        'Пиронен',
                        'Анна',
                        'ФСК "Могилев"',
                        1983,
                        'МС',
                        1,
                        '01:03:02',
                        1,
                    ],
                    4 => [
                        'Буякова',
                        'Оксана',
                        'КО «Сильван люкс»',
                        1990,
                        'МС',
                        23,
                        null,
                        null,
                        null,
                        null
                    ],
                ]
            ],
            [
                '2022/2022-07-16.html',
                192,
                [
                    12 => [
                        'Бондарев',
                        'Владислав',
                        'Локомотив',
                        null,
                        'б/р',
                        38,
                        '01:03:01',
                        13,
                    ],
                    37 => [
                        'Кириенков',     //lastname
                        'Родион',        //firstname
                        'Лагерь Волобо', //club
                        2009,            //year
                        'б/р',           //rank
                        189,             //runner_number
                        null,            //time
                        null,            //place
                        null,            //complete_rank
                        null,            //points
                    ],
                ]
            ],
            [
                '2023/150723.html',
                141,
                [
                    0 => [
                        'Пасютин', // фамилия
                        'Александр',  // имя
                        'Пеленг-Вымпел',    // клуб
                        1990,         // год
                        'б/р',         // разряд
                        144,          // номер
                        '0:36:43',    // время
                        1,            // место
                        null,         // выполненный разряд
                        null,          // очки
                    ],
                    100 => [
                        'Покульницкий',            // фамилия
                        'Алексей',                // имя
                        'лично', // клуб
                        1988,                  // год
                        'б/р',                 // разряд
                        107,                   // номер
                        null,                  // время
                        null,                  // место
                        null,                   // выполненный разряд
                        null,                  // очки
                    ],
                ],
                true
            ],
        ];
    }
    protected function getParser(): string
    {
        return OBelarusSpanParser::class;
    }
}
