<?php

namespace Tests\Integration\SimplyParser;

use App\Models\Parser\SimplyParser;
use Tests\Integration\AbstractParserTest;

class SimplyParser2Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return SimplyParser::class;
    }

    protected function getFilePath(): string
    {
        return '2019/protocol_191027_otkrytyy-kubok-minskogo-rayona-po-sportivnomu-orientirovaniyu-belaya-rus-2019_72005.htm';
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
                '0:12:57',
                1,
                '-',
                null,
            ],
            13 => [
                'Малышко',
                'Анна',
                'КСО «Белая Русь»',
                2013,
                'б/р',
                167,
                null,
                null,
                '-',
                null
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 329;
    }
}
