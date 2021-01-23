<?php

declare(strict_types=1);

namespace Tests\Integration\AlbatrosTimingParser;

use App\Models\Parser\AlbatrosTimingParser;
use Tests\Integration\AbstractParserTest;

class AlbatrosTimingParser1Test extends AbstractParserTest
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
