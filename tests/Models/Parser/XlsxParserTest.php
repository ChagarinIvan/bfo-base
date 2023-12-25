<?php
declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\XlsxParser;

class XlsxParserTest extends AbstractParserTest
{
    public static function dataProvider(): array
    {
        return [
            [
                '2023/270423.xlsx',
                161,
                [
                    0 => [
                        'Жданович',     //фамилия
                        'Кира',    //имя
                        'Березинский р-н',   //клуб
                        2009,        //год
                        'IIю',       //разряд
                        1,           //номер
                        '0:38:19',   //время
                        1,           //место
                        '1ю',       //выполненный разряд
                        100,       //очки
                    ],
                    160 => [
                        'Чижик',     //фамилия
                        'Алексей',    //имя
                        'Клецкий р-н',   //клуб
                        2006,        //год
                        'б/р',       //разряд
                        15,          //номер
                        null,        //время
                        null,        //место
                        null,        //очки
                        null,        //выполненный разряд
                    ],
                ],
                false,
                'openxmlformats'
            ],
            [
                '2023/130523.xlsx',
                91,
                [
                    0 => [
                        'Сорокин',     //фамилия
                        'Александр',    //имя
                        'СШ №25 г. Гомеля',   //клуб
                        2011,        //год
                        '1ю',       //разряд
                        222,           //номер
                        '00:14:43',   //время
                        1,           //место
                        '2ю',       //выполненный разряд
                        450,       //очки
                    ],
                    14 => [
                        'Кот',     //фамилия
                        'Степан',    //имя
                        'СШ №25 г. Гомеля-2',   //клуб
                        2011,        //год
                        null,       //разряд
                        224,          //номер
                        null,        //время
                        null,        //место
                        null,        //выполненный разряд
                        null,        //очки
                    ],
                    68 => [
                        'Лоев',     //фамилия
                        'Богдан',   //имя
                        'Носовичи', //клуб
                        2007,       //год
                        '2р',       //разряд
                        620,        //номер
                        '00:21:36', //время
                        1,          //место
                        '1ю',       //выполненный разряд
                        450,        //очки
                    ],
                ],
                false,
                'openxmlformats'
            ]
        ];
    }
    protected function getParser(): string
    {
        return XlsxParser::class;
    }
}
