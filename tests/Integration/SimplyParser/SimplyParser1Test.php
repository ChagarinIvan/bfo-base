<?php

namespace Tests\Integration\SimplyParser;

use App\Models\Parser\SimplyParser;
use Tests\Integration\AbstractParserTest;

class SimplyParser1Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return SimplyParser::class;
    }

    protected function getFilePath(): string
    {
        return '2019/protocol_191026_otkrytyy-kubok-minskogo-rayona-po-sportivnomu-orientirovaniyu-belaya-rus-2019_72004.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Холод',
                'Ирина',
                'СКО «Орион»',
                2009,
                'Iю',
                305,
                '0:08:39',
                1,
                '-',
                null,
            ],
            18 => [
                'Ярошевич',
                'Алена',
                'КО «Случь»',
                2011,
                'б/р',
                125,
                null,
                null,
                '-',
                null
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 319;
    }
}
