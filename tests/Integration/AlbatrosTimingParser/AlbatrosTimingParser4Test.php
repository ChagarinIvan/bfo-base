<?php

namespace Tests\Integration\AlbatrosTimingParser;

use App\Models\Parser\AlbatrosTimingParser;
use Tests\Integration\AbstractParserTest;

class AlbatrosTimingParser4Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return AlbatrosTimingParser::class;
    }

    protected function getFilePath(): string
    {
        return '2021/protocol_210522_komandnyy-chempionat_80865.htm';
    }

    protected function getResults(): array
    {
        return [
            232 => [
                'Алексеенок',
                'Алексей',
                'КСО «Эридан»',
                1988,
                'МС',
                383,
                '0:42:34',
                null,
                '-',
                null,
                true,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 349;
    }
}
