<?php

namespace Tests\Integration\AlbatrosTimingParser;

use App\Models\Parser\AlbatrosTimingParser;
use Tests\Integration\AbstractParserTest;

class AlbatrosTimingParser3Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return AlbatrosTimingParser::class;
    }

    protected function getFilePath(): string
    {
        return '2021/210306_res.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Буякова',
                'Оксана',
                'КО «Сильван люкс»',
                1990,
                'МС',
                9,
                '1:28:04',
                1,
                'МС',
                100,
            ],
            7 => [
                'Языкова',
                'Дарья',
                'КСО «Немига-Норд»',
                1993,
                'МС',
                2,
                null,
                null,
                '-',
                null
            ],
            13 => [
                'Лисаченко',
                'Ксения',
                'Эврика',
                2008,
                'Iю',
                19,
                '1:06:57',
                1,
                '-',
                null
            ],
            40 => [
                'Лисаченко',
                'Иван',
                'МГТЭЦДиМ',
                2005,
                'Iю',
                57,
                '0:44:45',
                1,
                null,
                null
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 52;
    }
}
