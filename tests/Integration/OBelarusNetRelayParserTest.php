<?php

namespace Tests\Integration;

use App\Models\Parser\OBelarusNetRelayParser;

class OBelarusNetRelayParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusNetRelayParser::class;
    }

    protected function getFilePath(): string
    {
        return '2020/200816_res.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Новиченко',
                'Антон',
                'КСО «Эридан»',
                1997,
                'МС',
                1001,
                '00:41:54',
                1,
                'МС',
                291,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 207;
    }
}
