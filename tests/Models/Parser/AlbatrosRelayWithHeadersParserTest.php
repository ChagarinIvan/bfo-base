<?php

namespace Tests\Models\Parser;

use App\Models\Parser\AlbatrosRelayWithHeadersParser;

class AlbatrosRelayWithHeadersParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return AlbatrosRelayWithHeadersParser::class;
    }

    public static function dataProvider(): array
    {
        return [
            [
                '2023/040623.html',
                102,
                [
                    0 => [
                        'Бильдюкевич', // фамилия
                        'Владислава',  // имя
                        'КО «Случь»',  // клуб
                        null,          // год
                        'КМС',         // разряд
                        1,             // номер
                        '00:18:12',    // время
                        1,             // место
                        'МС',          // выполненный разряд
                        394,           // очки
                    ],
                    40 => [
                        'Балабанов',    // фамилия
                        'Михаил',       // имя
                        'СК «Камволь»', // клуб
                        null,           // год
                        'I',            // разряд
                        8,              // номер
                        '00:17:28',     // время
                        null,           // место
                        null,           // выполненный разряд
                        null,           // очки
                        true,           // вк
                    ],
                    77 => [
                        'Горбунов',     // фамилия
                        'Игорь',        // имя
                        'СК «Камволь»', // клуб
                        null,           // год
                        'КМС',          // разряд
                        6,              // номер
                        null,           // время
                        null,           // место
                        null,           // выполненный разряд
                        null,           // очки
                    ],
                ]
            ],
        ];
    }
}
