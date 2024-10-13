<?php

declare(strict_types=1);

namespace Models\Parser;

use App\Models\Parser\AlbatrosTimingWithRegionParser;
use Tests\Models\Parser\AbstractParser;

final class AlbatrosTimingWithRegionParserTest extends AbstractParser
{
    public static function dataProvider(): array
    {
        return [
            [
                '2024/241006.htm',
                38,
                [
                    0 => [
                        'Дичковская',
                        'Алеся',
                        'КО «Случь»',
                        2003,
                        'МС',
                        52,
                        '1:50:14',
                        1,
                        'МС',
                        100,
                    ],
                    3 => [
                        'Павлова',
                        'Мария',
                        'RUS Калининград',
                        1984,
                        'б/р',
                        56,
                        '2:00:00',
                        null,
                        '-',
                        null,
                        true
                    ],
                    19 => [
                        'Крюков',
                        'Дмитрий',
                        'КСО «Немига-Норд»',
                        1988,
                        'МС',
                        5,
                        null,
                        null,
                        '-',
                        null,
                    ],
                ]
            ],
        ];
    }
    protected function getParser(): string
    {
        return AlbatrosTimingWithRegionParser::class;
    }
}
