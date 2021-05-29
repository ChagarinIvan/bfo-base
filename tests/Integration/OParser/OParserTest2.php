<?php

namespace Tests\Integration\OParser;

use App\Models\Parser\OParser;
use Tests\Integration\AbstractParserTest;

class OParserTest2 extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OParser::class;
    }

    protected function getFilePath(): string
    {
        return '2021/20210403_kl_brest.html';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Холод',
                'Ирина',
                'СКО Орион',
                2009,
                'б/р',
                505,
                '0:20:23',
                1,
                'IIю',
                1000,
            ],
            15 => [
                'Мурашко',
                'Злата',
                'СКО Орион',
                2013,
                'б/р',
                700,
                '0:43:19',
                null,
                '-',
                null,
                true,
            ],
            42 => [
                'Мурашко',
                'Полина',
                'СКО Орион',
                2012,
                'б/р',
                701,
                null,
                null,
                '-',
                null,
                true
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 530;
    }
}
