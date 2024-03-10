<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\XlsParser;

final class XlsParserTest extends AbstractParser
{
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
            ],
            [
                '2023/16062023.xls',
                110,
                [
                    0 => [
                        'Корнелюк',     //фамилия
                        'Вячеслав',    //имя
                        'Гродно Дворец пионеров',   //клуб
                        2014,        //год
                        'IIю',       //разряд
                        1,           //номер
                        '0:14:23',   //время
                        1,           //место
                        'IIю',       //выполненный разряд
                    ],
                    19 => [
                        'Котовский',     //фамилия
                        'Арсений',    //имя
                        'Лидский район сш 9',   //клуб
                        2014,        //год
                        'б/р',       //разряд
                        20,          //номер
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
    protected function getParser(): string
    {
        return XlsParser::class;
    }
}
