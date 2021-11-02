<?php

namespace Tests\Integration\HandicapAlbatrosTimingParser;

use App\Models\Parser\HandicapAlbatrosTimingParser;
use Tests\Integration\AbstractParserTest;

class HandicapTimingParser1Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return HandicapAlbatrosTimingParser::class;
    }

    protected function getFilePath(): string
    {
        return '2019/191110_ResultList.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Волосевич',
                'Александра',
                'КО «Случь»',
                2010,
                'б/р',
                3,
                '0:25:08',
                1,
                null,
                null,
            ],
            36 => [
                'Жевнерович',
                'Анна',
                'Березинский р-н',
                2006,
                'б/р',
                147,
                null,
                null,
                null,
                null
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 184;
    }
}
