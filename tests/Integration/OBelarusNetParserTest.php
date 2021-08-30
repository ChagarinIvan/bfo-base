<?php

namespace Tests\Integration;

use App\Models\Parser\OBelarusNetParser;

class OBelarusNetParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusNetParser::class;
    }

    protected function getFilePath(): string
    {
        return '2021/protocol_210821_kubok-belarusi_84462.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Михалкин',
                'Дмитрий',
                'КСО «Эридан»',
                1980,
                'МС',
                48,
                '1:30:42',
                1,
                'МС',
                100,
            ],
            1 => [
                'Минаков',
                'Александр',
                'RUS КСО «Москомпас»',
                1982,
                'б/р',
                156,
                '1:33:53',
                null,
                '-',
                null,
                true
            ],
            64 => [
                'Буковец',
                'Артём',
                'КСО «Три-О»',
                null,
                'б/р',
                802,
                '1:24:17',
                11,
                '-',
                29,
            ],
            82 => [
                'Буковец',
                'Анатолий',
                'КСО «Три-О»',
                1950,
                'I',
                81,
                null,
                null,
                '-',
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 127;
    }
}
