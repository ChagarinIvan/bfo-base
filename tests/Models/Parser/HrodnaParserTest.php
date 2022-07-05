<?php

namespace Tests\Models\Parser;

use App\Models\Parser\HrodnoParser;

class HrodnaParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return HrodnoParser::class;
    }

    public function testData(): array
    {
        return [
            [
                '2021/results_sprint_razr_09.10.2021_Спринт.html',
                112,
                [
                    0 => [
                        'Ковшик',
                        'Федор',
                        'Бр сан шк-инт',
                        2009,
                        'IIю',
                        0,
                        '0:12:36',
                        1,
                        'Iю',
                        null,
                    ],
                    15 => [
                        'Игнатов',
                        'Федор',
                        'КСО Кронан',
                        2011,
                        'б/р',
                        0,
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
