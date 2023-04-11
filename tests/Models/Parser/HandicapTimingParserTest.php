<?php

namespace Tests\Models\Parser;

use App\Models\Parser\HandicapAlbatrosTimingParser;

class HandicapTimingParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return HandicapAlbatrosTimingParser::class;
    }

    public static function dataProvider(): array
    {
        return [
            [
                '2019/191110_ResultList.htm',
                184,
                [
                    0 => [
                        'Волосевич',
                        'Александра',
                        'КО «Случь»',
                        2010,
                        'б/р',
                        3,
                        '0:25:08',
                        1,
                        null,
                        null,
                    ],
                    36 => [
                        'Жевнерович',
                        'Анна',
                        'Березинский р-н',
                        2006,
                        'б/р',
                        147,
                        null,
                        null,
                        null,
                        null
                    ],
                ]
            ],
            [
                '2019/protocol_190929_bgu-98_59449.htm',
                114,
                [
                    0 => [
                        'Малалетников',
                        'Павел',
                        'Лично',
                        1982,
                        'б/р',
                        3102,
                        '2:00:59',
                        1,
                        null,
                        null,
                    ],
                    2 => [
                        'Стебеняева',
                        'Алёна',
                        'КО «Легенда»',
                        1974,
                        'б/р',
                        3105,
                        null,
                        null,
                        null,
                        null,
                    ],
                    79 => [
                        'Михалкин',
                        'Дмитрий',
                        'КСО «Эридан»',
                        1980,
                        'МС',
                        2010,
                        '1:22:53',
                        1,
                        'КМС',
                        null
                    ],
                    90 => [
                        'Лычков',
                        'Игорь',
                        'КСО «Немига-Норд»',
                        1983,
                        'КМС',
                        2006,
                        null,
                        null,
                        null,
                        null
                    ],
                ]
            ]
        ];
    }
}
