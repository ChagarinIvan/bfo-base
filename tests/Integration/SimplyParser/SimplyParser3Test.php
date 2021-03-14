<?php

declare(strict_types=1);

namespace Tests\Integration\SimplyParser;

use App\Models\Parser\SimplyParser;
use Tests\Integration\AbstractParserTest;

class SimplyParser3Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return SimplyParser::class;
    }

    protected function getFilePath(): string
    {
        return '2019/23032019.htm';
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
                78,
                '00:06:42',
                1,
                '-',
                null,
            ],
            41 => [
                'Дичковская',
                'Алеся',
                'КО «Случь»',
                2003,
                'I',
                274,
                null,
                null,
                '-',
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 296;
    }
}
