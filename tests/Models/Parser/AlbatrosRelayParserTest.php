<?php

namespace Tests\Models\Parser;

use App\Models\Parser\AlbatrosRelayParser;

class AlbatrosRelayParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return AlbatrosRelayParser::class;
    }

    public function dataProvider(): array
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
            ]
        ];
    }
}
