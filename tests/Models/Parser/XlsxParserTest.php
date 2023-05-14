<?php

namespace Tests\Models\Parser;

use App\Models\Parser\XlsxParser;

class XlsxParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return XlsxParser::class;
    }

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
            ]
        ];
    }
}
