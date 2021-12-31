<?php

namespace Tests\Integration;

use App\Models\Parser\HrodnoParser;

class HrodnaParser1Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return HrodnoParser::class;
    }

    protected function getFilePath(): string
    {
        return '2021/results_sprint_razr_09.10.2021_Спринт.html';
    }

    protected function getResults(): array
    {
        return [
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
        ];
    }

    protected function geLinesCount(): int
    {
        return 112;
    }
}
