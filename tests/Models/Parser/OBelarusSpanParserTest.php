<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\OBelarusSpanParser;

class OBelarusSpanParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusSpanParser::class;
    }

    public function dataProvider(): array
    {
        return [
            [
                '2022/20220203.html',
                171,
                [
                    0 => [
                        'Волосевич',
                        'Александра',
                        'Слуцкий р-н',
                        2010,
                        'Iю',
                        119,
                        '00:22:22',
                        1,
                    ],
                    17 => [
                        'Бурма',
                        'Елизавета',
                        'Логойский р-н',
                        2010,
                        'б/р',
                        98,
                        '01:19:42',
                        null,
                        null,
                        null,
                        true
                    ],
                    18 => [
                        'Плис',
                        'Евгения',
                        'Березинский р-н',
                        null,
                        'б/р',
                        200,
                        null,
                        null,
                        null,
                        null,
                    ],
                    19 => [
                        'Примаченок',
                        'Дарья',
                        'Березинский р-н',
                        2009,
                        'Iю',
                        39,
                        null,
                        null,
                        null,
                        null,
                    ],
                ]
            ],
            [
                '2022-07-09.htm',
                76,
                [
                    0 => [
                        'Пиронен',
                        'Анна',
                        'ФСК "Могилев"',
                        1983,
                        'МС',
                        1,
                        '01:03:02',
                        1,
                    ],
                    4 => [
                        'Буякова',
                        'Оксана',
                        'КО «Сильван люкс»',
                        1990,
                        'MC',
                        23,
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
