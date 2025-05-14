<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\SFRParser;

final class SfrParserTest extends AbstractParser
{
    public static function dataProvider(): array
    {
        return [
            [
                '2023/26.08.2023.htm',
                100,
                [
                    0 => [
                        'Уланов',
                        'Андрей',
                        'PULS-Nadejda',
                        null,
                        null,
                        290,
                        '00:17:08',
                        1,
                        null,
                        null,
                    ],
                ],
            ],
            [
                '2025/27042025.htm',
                46,
                [
                    0 => [
                        'Рубан',        // фамилия
                        'Андрей',     // имя
                        'ГОЦТЭДиМ', // клуб
                        2011,           // год
                        '2р',           // разряд
                        607,            // номер
                        '00:40:55',     // время
                        1,              // место
                        null,           // выполненный разряд
                        100,            // очки
                    ],
                ],
            ],
            [
                '2024/12102024.htm',
                219,
                [
                    0 => [
                        'Мигун',        // фамилия
                        'Василиса',     // имя
                        'КО «Случь»-2', // клуб
                        2017,           // год
                        null,           // разряд
                        305,            // номер
                        '00:12:43',     // время
                        1,              // место
                        '2ю',           // выполненный разряд
                        500,            // очки
                    ],
                    14 => [
                        'Бондаренко',       // фамилия
                        'Екатерина',        // имя
                        'СШ №25 г. Гомеля', // клуб
                        2017,               // год
                        null,               // разряд
                        475,                // номер
                        null,
                        null,
                        null,
                    ],
                ],
                'htm'
            ]
        ];
    }
    protected function getParser(): string
    {
        return SFRParser::class;
    }
}
