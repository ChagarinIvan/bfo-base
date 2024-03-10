<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\AlbatrosRelayParser;

final class AlbatrosRelayParserTest extends AbstractParser
{
    public static function dataProvider(): array
    {
        return [
            [
                '2021/210822r-official.htm',
                124,
                [
                    0 => [
                        'Жаховский',
                        'Евгений',
                        'КСО «Эридан»',
                        null,
                        'МС',
                        107,
                        '00:43:10',
                        1,
                        'МС',
                        291,
                    ],
                    30 => [
                        'Ходан',
                        'Александр',
                        'СК «Камволь»',
                        null,
                        'КМС',
                        105,
                        '00:56:03',
                        11,
                        null,
                        null
                    ],
                    33 => [
                        'Акулич',
                        'Александр',
                        'лично',
                        null,
                        'I',
                        111,
                        '01:04:39',
                        13,
                        null,
                        null,
                        true
                    ],
                ]
            ],
            [
                '2022/2022-06-26.htm',
                108,
                [
                    0 => [
                        'Каржова',
                        'Марина',
                        'КСО "Немига-Норд"',
                        null,
                        'МС',
                        17,
                        '00:19:31',
                        1,
                        'МС',
                        1,
                    ],
                    50 => [
                        'Минчуков',
                        'Александр',
                        'СК "Камволь"',
                        null,
                        'I',
                        21,
                        null,
                        13,
                        null,
                        null
                    ],
                    51 => [
                        'Сакута',
                        'Виктор',
                        'СК "Камволь"',
                        null,
                        'II',
                        21,
                        null,
                        13,
                        null,
                        null
                    ],
                ]
            ]
        ];
    }
    protected function getParser(): string
    {
        return AlbatrosRelayParser::class;
    }
}
