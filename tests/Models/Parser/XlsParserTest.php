<?php

namespace Tests\Models\Parser;

use App\Models\Parser\XlsParser;

class XlsParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return XlsParser::class;
    }

    public static function dataProvider(): array
    {
        return [
            [
                '2023/08042023.xls',
                117,
                [
                    0 => [
                        'Бурдюк',     //фамилия
                        'Тимофей',    //имя
                        '"ВЦТДиМ" г. Волковыск',   //клуб
                        2011,        //год
                        'IIю',       //разряд
                        1,           //номер
                        '0:19:16',   //время
                        1,           //место
                        'IIю',       //выполненный разряд
                    ],
                    64 => [
                        'Лобач',     //фамилия
                        'Максим',    //имя
                        '"ВЦТДиМ" г. Волковыск',   //клуб
                        null,        //год
                        'б/р',       //разряд
                        22,          //номер
                        null,        //время
                        null,        //место
                        null,        //очки
                        null,        //выполненный разряд
                    ],
                ],
                false,
                'excel'
            ]
        ];
    }
}
