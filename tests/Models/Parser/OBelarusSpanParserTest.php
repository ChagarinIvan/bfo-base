<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\OBelarusSpanParser;

final class OBelarusSpanParserTest extends AbstractParser
{
    public static function dataProvider(): array
    {
        return [
            [
                '2024/20240127.html',
                98,
                [
                    97 => [
                        'Арешков',
                        'Владислав',
                        'КСО «Березино»',
                        2014,
                        'б/р',
                        99,
                        null,
                        null,
                        null,
                        null,
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
