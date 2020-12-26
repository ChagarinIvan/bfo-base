<?php

namespace Tests\Integration;

use App\Models\Parser\AlbatrosTimingParser;

class AlbatrosTimingParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return AlbatrosTimingParser::class;
    }

    protected function getFilePath(): string
    {
        return '2020/200905_perv_00_.htm';
    }

    protected function getResults(): array
    {
        return [
            7 => [
                'Сукневич',
                'Миша',
                'КСО «Немига-Норд»',
                2010,
                'б/р',
                151,
                '0:35:43',
                8,
                '-',
                null,
            ],
            20 => [
                'Лабкович',
                'Иван',
                'Минская обл.',
                2007,
                'Iю',
                444,
                '0:31:24',
                2,
                'IIю',
                100
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 118;
    }
}
