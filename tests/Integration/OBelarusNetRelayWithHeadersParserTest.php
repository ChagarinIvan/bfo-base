<?php

namespace Tests\Integration;

use App\Models\Parser\OBelarusNetRelayWithHeadersParser;

class OBelarusNetRelayWithHeadersParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusNetRelayWithHeadersParser::class;
    }

    protected function getFilePath(): string
    {
        return '2020/CHempionat_Belarusi_estafeta_BFO.html';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Кругленя',
                'Анастасия',
                'СК «Камволь»',
                null,
                'МС',
                121,
                '00:14:43',
                1,
                'МС',
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 98;
    }
}
